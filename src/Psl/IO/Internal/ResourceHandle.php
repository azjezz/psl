<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Async;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function error_get_last;
use function fclose;
use function fseek;
use function ftell;
use function fwrite;
use function is_resource;
use function max;
use function str_contains;
use function stream_get_meta_data;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function substr;

/**
 * @internal
 *
 * @psalm-suppress PossiblyInvalidArgument
 * @psalm-suppress MissingThrowsDocblock
 *
 * @codeCoverageIgnore
 */
class ResourceHandle implements IO\CloseSeekReadWriteStreamHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    public const DEFAULT_READ_BUFFER_SIZE = 4096;
    public const MAXIMUM_READ_BUFFER_SIZE = 786432;

    /**
     * @var closed-resource|resource|null $stream
     */
    protected mixed $stream;

    /**
     * @var null|Async\Sequence<array{null|int<1, max>, null|float}, string>
     */
    private ?Async\Sequence $readSequence = null;

    private ?Suspension $readSuspension = null;

    /**
     * @var string
     */
    private string $readWatcher = 'invalid';

    /**
     * @var null|Async\Sequence<array{string, null|float}, int<0, max>>
     */
    private ?Async\Sequence $writeSequence = null;

    private ?Suspension $writeSuspension = null;

    /**
     * @var string
     */
    private string $writeWatcher = 'invalid';

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream, bool $read, bool $write, bool $seek, private bool $close)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType - The stream is always a resource, but we want to make sure it is a stream resource. */
        $this->stream = Type\resource('stream')->assert($stream);

        /** @psalm-suppress UnusedFunctionCall */
        stream_set_read_buffer($stream, 0);
        stream_set_blocking($stream, false);

        $meta = stream_get_meta_data($stream);
        $blocks = $meta['blocked'] || ($meta['wrapper_type'] ?? '') === 'plainfile';
        if ($seek) {
            Psl\invariant($meta['seekable'], 'Handle is not seekable.');
        }

        if ($read) {
            Psl\invariant(str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+'), 'Handle is not readable.');

            $this->readWatcher = EventLoop::onReadable($this->stream, function () {
                $this->readSuspension?->resume(null);
            });
            $this->readSequence = new Async\Sequence(
                /**
                 * @param array{null|int<1, max>, null|float} $input
                 */
                function (array $input) use ($blocks): string {
                    [$max_bytes, $timeout] = $input;
                    $chunk = $this->tryRead($max_bytes);
                    if ('' !== $chunk || $blocks) {
                        return $chunk;
                    }

                    $this->readSuspension = $suspension = EventLoop::getSuspension();
                    EventLoop::enable($this->readWatcher);
                    $delay_watcher = null;
                    if (null !== $timeout) {
                        $timeout = max($timeout, 0.0);
                        $delay_watcher = EventLoop::delay(
                            $timeout,
                            static fn () => $suspension->throw(new Exception\TimeoutException('Reached timeout while the handle is still not readable.')),
                        );

                        EventLoop::unreference($delay_watcher);
                    }

                    try {
                        $suspension->suspend();

                        return $this->tryRead($max_bytes);
                    } finally {
                        $this->readSuspension = null;
                        EventLoop::disable($this->readWatcher);
                        if (null !== $delay_watcher) {
                            EventLoop::cancel($delay_watcher);
                        }
                    }
                }
            );

            EventLoop::disable($this->readWatcher);
        }

        if ($write) {
            $writable = str_contains($meta['mode'], 'x')
                || str_contains($meta['mode'], 'w')
                || str_contains($meta['mode'], 'c')
                || str_contains($meta['mode'], 'a')
                || str_contains($meta['mode'], '+');

              Psl\invariant($writable, 'Handle is not writeable.');

            $this->writeWatcher = EventLoop::onReadable($this->stream, function () {
                $this->writeSuspension?->resume(null);
            });
            $this->writeSequence = new Async\Sequence(
                /**
                 * @param array{string, null|float} $input
                 *
                 * @return int<0, max>
                 */
                function (array $input) use ($blocks): int {
                    [$bytes, $timeout] = $input;
                    $written = $this->tryWrite($bytes);
                    $remaining_bytes = substr($bytes, $written);
                    if ($blocks || '' === $remaining_bytes) {
                        return $written;
                    }

                    $this->writeSuspension = $suspension = EventLoop::getSuspension();
                    EventLoop::enable($this->writeWatcher);
                    $delay_watcher = null;
                    if (null !== $timeout) {
                        $timeout = max($timeout, 0.0);
                        $delay_watcher = EventLoop::delay(
                            $timeout,
                            static fn () => $suspension->throw(new Exception\TimeoutException('Reached timeout while the handle is still not readable.')),
                        );

                        EventLoop::unreference($delay_watcher);
                    }

                    try {
                        $suspension->suspend();

                        return $written + $this->tryWrite($remaining_bytes);
                    } finally {
                        $this->writeSuspension = null;
                        EventLoop::disable($this->writeWatcher);
                        if (null !== $delay_watcher) {
                            EventLoop::cancel($delay_watcher);
                        }
                    }
                },
            );
            EventLoop::disable($this->writeWatcher);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        Psl\invariant($this->writeSequence !== null, 'The resource handle is not writable.');

        return $this->writeSequence->waitFor([$bytes, $timeout]);
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $result = @fwrite($this->stream, $bytes);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return max($result, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function seek(int $offset): void
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $result = @fseek($this->stream, $offset);
        if (0 !== $result) {
            throw new Exception\RuntimeException('Failed to seek the specified position.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $result = @ftell($this->stream);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return max($result, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        Psl\invariant($this->readSequence !== null, 'The resource handle is not readable.');

        return $this->readSequence->waitFor([$max_bytes, $timeout]);
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(?int $max_bytes = null): string
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        if ($max_bytes === null) {
            $max_bytes = self::DEFAULT_READ_BUFFER_SIZE;
        } elseif ($max_bytes > self::MAXIMUM_READ_BUFFER_SIZE) {
            $max_bytes = self::MAXIMUM_READ_BUFFER_SIZE;
        }

        $result = fread($this->stream, $max_bytes);
        if ($result === false) {
            /** @var array{message: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        /** @var resource|null */
        return $this->stream;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        EventLoop::cancel($this->readWatcher);
        EventLoop::cancel($this->writeWatcher);
        if (null !== $this->stream) {
            $exception = new Exception\AlreadyClosedException('Handle has already been closed.');

            $this->readSequence?->cancel($exception);
            $this->readSuspension?->throw($exception);

            $this->writeSequence?->cancel($exception);
            $this->writeSuspension?->throw($exception);

            // don't close the stream if `$this->close` is false, or if it's already closed.
            if ($this->close && is_resource($this->stream)) {
                $stream = $this->stream;
                $this->stream = null;
                $result = @fclose($stream);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }
            } else {
                // Stream could be set to a non-null closed-resource,
                // if manually closed using `fclose($handle->getStream)`.
                $this->stream = null;
            }
        }
    }
}
