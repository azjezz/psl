<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Override;
use Psl;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function error_get_last;
use function fclose;
use function feof;
use function fread;
use function fseek;
use function ftell;
use function fwrite;
use function is_resource;
use function max;
use function str_contains;
use function stream_get_contents;
use function stream_get_meta_data;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function stream_set_write_buffer;
use function substr;

/**
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @mago-expect lint:best-practices/no-else-clause
 */
class ResourceHandle implements
    IO\ReadHandleInterface,
    IO\WriteHandleInterface,
    IO\SeekHandleInterface,
    IO\CloseHandleInterface,
    IO\StreamHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    public const int DEFAULT_READ_BUFFER_SIZE = 4096;
    public const int MAXIMUM_READ_BUFFER_SIZE = 786432;

    /**
     * @var closed-resource|resource|null $stream
     */
    protected mixed $stream;

    /**
     * @var null|Async\Sequence<array{string, null|Duration}, int<0, max>>
     */
    private null|Async\Sequence $writeSequence = null;
    private null|Suspension $writeSuspension = null;
    private string $writeWatcher = 'invalid';

    /**
     * @var null|Async\Sequence<array{null|int<1, max>, null|Duration}, string>
     */
    private null|Async\Sequence $readSequence = null;
    private null|Suspension $readSuspension = null;
    private string $readWatcher = 'invalid';

    private bool $useSingleRead = false;
    private bool $reachedEof = false;

    /**
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        bool $read,
        bool $write,
        bool $seek,
        private readonly bool $close,
    ) {
        $this->stream = Type\resource('stream')->assert($stream);

        stream_set_blocking($stream, false);

        $meta = stream_get_meta_data($stream);
        if ($read) {
            $this->useSingleRead = 'udp_socket' === $meta['stream_type'] || 'STDIO' === $meta['stream_type'];
        }

        $blocks = $meta['blocked'] || ($meta['wrapper_type'] ?? '') === 'plainfile';
        if ($seek) {
            $seekable = $meta['seekable'];

            Psl\invariant($seekable, 'Handle is not seekable.');
        }

        if ($read) {
            $readable = str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');

            Psl\invariant($readable, 'Handle is not readable.');

            stream_set_read_buffer($stream, 0);

            $this->readWatcher = EventLoop::onReadable($this->stream, function (): void {
                $this->readSuspension?->resume();
            });

            $this->readSequence = new Async\Sequence(
                /**
                 * @param array{null|int<1, max>, null|Duration} $input
                 */
                function (array $input) use ($blocks): string {
                    [$max_bytes, $timeout] = $input;
                    $chunk = $this->tryRead($max_bytes);
                    if ('' !== $chunk || $blocks) {
                        return $chunk;
                    }

                    $suspension = EventLoop::getSuspension();
                    $this->readSuspension = $suspension;
                    EventLoop::enable($this->readWatcher);
                    $delay_watcher = null;
                    if (null !== $timeout) {
                        $timeout = max($timeout->getTotalSeconds(), 0.0);
                        $delay_watcher = EventLoop::delay($timeout, static fn(): null => $suspension->throw(
                            new Exception\TimeoutException('Reached timeout while the handle is still not readable.'),
                        ));

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
                },
            );

            EventLoop::disable($this->readWatcher);
        }

        if ($write) {
            $writable =
                str_contains($meta['mode'], 'x')
                || str_contains($meta['mode'], 'w')
                || str_contains($meta['mode'], 'c')
                || str_contains($meta['mode'], 'a')
                || str_contains($meta['mode'], '+');

            Psl\invariant($writable, 'Handle is not writeable.');

            stream_set_write_buffer($stream, 0);

            $this->writeWatcher = EventLoop::onWritable($this->stream, function (): void {
                $this->writeSuspension?->resume();
            });

            $this->writeSequence = new Async\Sequence(
                /**
                 * @param array{string, null|Duration} $input
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

                    $suspension = EventLoop::getSuspension();
                    $this->writeSuspension = $suspension;
                    EventLoop::enable($this->writeWatcher);
                    $delay_watcher = null;
                    if (null !== $timeout) {
                        $timeout = max($timeout->getTotalSeconds(), 0.0);
                        $delay_watcher = EventLoop::delay($timeout, static fn(): null => $suspension->throw(
                            new Exception\TimeoutException('Reached timeout while the handle is still not readable.'),
                        ));

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
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        Psl\invariant(null !== $this->writeSequence, 'The resource handle is not writable.');

        return $this->writeSequence->waitFor([$bytes, $timeout]);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $result = @fwrite($this->stream, $bytes);
        if (false === $result) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return max($result, 0);
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tell(): int
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $result = @ftell($this->stream);
        if (false === $result) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return max($result, 0);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        if ($this->reachedEof) {
            return true;
        }

        return $this->reachedEof = feof($this->stream);
    }

    /**
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        if (null === $max_bytes) {
            $max_bytes = self::DEFAULT_READ_BUFFER_SIZE;
        } elseif ($max_bytes > self::MAXIMUM_READ_BUFFER_SIZE) {
            $max_bytes = self::MAXIMUM_READ_BUFFER_SIZE;
        }

        if ($this->useSingleRead) {
            $result = fread($this->stream, $max_bytes);
        } else {
            $result = stream_get_contents($this->stream, $max_bytes);
        }

        if (false === $result) {
            /** @var array{message?: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        if ('' === $result && feof($this->stream)) {
            $this->reachedEof = true;
        }

        return $result;
    }

    /**
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        Psl\invariant(null !== $this->readSequence, 'The resource handle is not readable.');

        return $this->readSequence->waitFor([$max_bytes, $timeout]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
                if (false === $result) {
                    /** @var array{message?: string} $error */
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return;
            }

            // Stream could be set to a non-null closed-resource,
            // if manually closed using `fclose($handle->getStream)`.
            $this->stream = null;
        }
    }

    /**
     * @return resource|null
     *
     * @inheritDoc
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function __destruct()
    {
        $this->close();
    }
}
