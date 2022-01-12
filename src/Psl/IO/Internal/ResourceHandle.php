<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Async;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;
use Revolt\EventLoop\Suspension;

use function array_shift;
use function array_slice;
use function error_get_last;
use function fclose;
use function fseek;
use function ftell;
use function fwrite;
use function is_resource;
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
     * @var object|resource|null $stream
     */
    protected mixed $stream;

    private bool $blocks;

    /**
     * @var non-empty-string
     */
    private string $readWatcher = 'invalid';
    private bool $reading = false;
    private ?Suspension $readSuspension = null;
    /**
     * @var list<Suspension>
     */
    private array $readQueue = [];

    /**
     * @var non-empty-string
     */
    private string $writeWatcher = 'invalid';
    private bool $writing = false;
    private ?Suspension $writeSuspension = null;
    /**
     * @var list<Suspension>
     */
    private array $writeQueue = [];

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream, bool $read, bool $write, bool $seek, private bool $close)
    {
        $this->stream = Type\union(
            Type\resource('stream'),
            Type\object(),
        )->assert($stream);

        /** @psalm-suppress UnusedFunctionCall */
        stream_set_read_buffer($stream, 0);
        stream_set_blocking($stream, false);

        $meta = stream_get_meta_data($stream);
        $this->blocks = $meta['blocked'] || ($meta['wrapper_type'] ?? '') === 'plainfile';
        if ($seek) {
            Psl\invariant($meta['seekable'], 'Handle is not seekable.');
        }

        if ($read) {
            Psl\invariant(str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+'), 'Handle is not readable.');

            $suspension = &$this->readSuspension;
            $this->readWatcher = Async\Scheduler::onReadable($stream, static function () use (&$suspension) {
                /** @var Suspension|null $tmp */
                $tmp = $suspension;
                $suspension = null;
                $tmp?->resume();
            });

            Async\Scheduler::disable($this->readWatcher);
        }

        if ($write) {
            $writable = str_contains($meta['mode'], 'x')
                || str_contains($meta['mode'], 'w')
                || str_contains($meta['mode'], 'c')
                || str_contains($meta['mode'], 'a')
                || str_contains($meta['mode'], '+');

            Psl\invariant($writable, 'Handle is not writeable.');

            $suspension = &$this->writeSuspension;
            $this->writeWatcher = Async\Scheduler::onWritable($stream, static function () use (&$suspension) {
                /** @var Suspension|null $tmp */
                $tmp = $suspension;
                $suspension = null;
                $tmp?->resume();
            });

            Async\Scheduler::disable($this->writeWatcher);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        // there's a pending read operation, wait for it.
        if ($this->writing) {
            $suspension = Async\Scheduler::createSuspension();
            $this->writeQueue[] = $suspension;
            $suspension->suspend();
        }

        // block incoming operations.
        $this->writing = true;
        $delay_watcher = null;

        try {
            $written = $this->tryWrite($bytes);
            $remaining_bytes = substr($bytes, $written);
            if ($this->blocks || '' === $remaining_bytes) {
                return $written;
            }

            $this->writeSuspension = Async\Scheduler::createSuspension();
            /** @psalm-suppress MissingThrowsDocblock */
            Async\Scheduler::enable($this->writeWatcher);
            if (null !== $timeout) {
                $timeout = $timeout < 0.0 ? 0.0 : $timeout;
                $delay_watcher = Async\Scheduler::delay(
                    $timeout,
                    fn () => $this->writeSuspension?->throw(new Exception\TimeoutException('Reached timeout while the handle is still not writable.')),
                );

                Async\Scheduler::unreference($delay_watcher);
            }

            $this->writeSuspension?->suspend();

            return $written + $this->tryWrite($remaining_bytes);
        } finally {
            $this->writeSuspension = null;
            Async\Scheduler::disable($this->writeWatcher);
            if (null !== $delay_watcher) {
                Async\Scheduler::cancel($delay_watcher);
            }

            $suspension = array_shift($this->writeQueue);
            if ($suspension !== null) {
                $suspension->resume();
            } else {
                $this->writing = false;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fwrite($this->stream, $bytes);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result >= 0 ? $result : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function seek(int $offset): void
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
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

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @ftell($this->stream);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result >= 0 ? $result : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        // there's a pending read operation, wait for it.
        if ($this->reading) {
            $suspension = Async\Scheduler::createSuspension();
            $this->readQueue[] = $suspension;
            $suspension->suspend();
        }

        // block incoming operations.
        $this->reading = true;
        $delay_watcher = null;

        try {
            $chunk = $this->tryRead($max_bytes);
            if ('' !== $chunk || $this->blocks) {
                return $chunk;
            }

            $this->readSuspension = Async\Scheduler::createSuspension();
            /** @psalm-suppress MissingThrowsDocblock */
            Async\Scheduler::enable($this->readWatcher);
            if (null !== $timeout) {
                $timeout = $timeout < 0.0 ? 0.0 : $timeout;
                $delay_watcher = Async\Scheduler::delay(
                    $timeout,
                    function () {
                        $this->readSuspension?->throw(new Exception\TimeoutException('Reached timeout while the handle is still not readable.'));
                    }
                );

                Async\Scheduler::unreference($delay_watcher);
            }

            $this->readSuspension?->suspend();

            return $this->tryRead($max_bytes);
        } finally {
            $this->readSuspension = null;
            Async\Scheduler::disable($this->readWatcher);
            if (null !== $delay_watcher) {
                Async\Scheduler::cancel($delay_watcher);
            }

            $suspension = array_shift($this->readQueue);
            if ($suspension !== null) {
                $suspension->resume();
            } else {
                $this->reading = false;
            }
        }
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

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = fread($this->stream, $max_bytes);
        if ($result === false) {
            /** @var array{message: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result;
    }

    /**
     * @return object|resource|null
     */
    public function getStream(): mixed
    {
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
        if (null !== $this->stream) {
            Async\Scheduler::disable($this->readWatcher);
            Async\Scheduler::disable($this->writeWatcher);

            // don't close the stream if `$this->close` is false, or if it's already closed.
            if ($this->close && is_resource($this->stream)) {
                /** @psalm-suppress PossiblyInvalidArgument */
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

            $this->readSuspension?->throw(new Exception\AlreadyClosedException('Handle has already been closed.'));
            $this->readSuspension = null;

            $this->writeSuspension?->throw(new Exception\AlreadyClosedException('Handle has already been closed.'));
            $this->writeSuspension = null;
        }
    }
}
