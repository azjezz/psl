<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Async;
use Psl\Exception\InvariantViolationException;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;
use Revolt\EventLoop;

use function error_get_last;
use function fclose;
use function fseek;
use function ftell;
use function fwrite;
use function is_resource;
use function str_contains;
use function stream_get_contents;
use function stream_get_meta_data;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function strlen;
use function substr;

/**
 * @internal
 *
 * @psalm-suppress PossiblyInvalidArgument
 * @psalm-suppress MissingThrowsDocblock
 *
 * @codeCoverageIgnore
 */
class ResourceHandle implements IO\Stream\CloseSeekReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    public const DEFAULT_READ_BUFFER_SIZE = 4096;
    public const MAXIMUM_READ_BUFFER_SIZE = 786432;

    /**
     * @var object|resource|null $stream
     */
    protected mixed $stream;

    private bool $useSingleRead;

    private bool $blocks;

    private string $readWatcher = '';
    private string $writeWatcher = '';

    private ?Async\Deferred $readDeferred = null;
    private ?Async\Deferred $writeDeferred = null;

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream, bool $read, bool $write, bool $seek)
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
            $seekable = (bool)$meta['seekable'];

            Psl\invariant($seekable, 'Handle is not seekable.');
        }

        if ($read) {
            $readable = str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');

            Psl\invariant($readable, 'Handle is not readable.');

            $deferred = &$this->readDeferred;
            $this->readWatcher = EventLoop::onReadable($stream, static function () use (&$deferred) {
                /** @var Async\Deferred|null $deferred */
                $deferred?->complete(null);
            });

            EventLoop::disable($this->readWatcher);
        }

        if ($write) {
            $writable = str_contains($meta['mode'], 'x')
                || str_contains($meta['mode'], 'w')
                || str_contains($meta['mode'], 'c')
                || str_contains($meta['mode'], 'a')
                || str_contains($meta['mode'], '+');

            Psl\invariant($writable, 'Handle is not writeable.');

            $deferred = &$this->writeDeferred;
            $this->writeWatcher = EventLoop::onWritable($stream, static function () use (&$deferred) {
                /** @var Async\Deferred|null $deferred */
                $deferred?->complete(null);
            });

            EventLoop::disable($this->writeWatcher);
        }

        $this->useSingleRead = $meta["stream_type"] === "udp_socket" || $meta["stream_type"] === "STDIO";
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $timeout is negative.
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        Psl\invariant(
            $timeout === null || $timeout > 0,
            '$timeout must be null, or > 0',
        );

        $written = $this->writeImmediately($bytes);
        if ($this->blocks || $written === strlen($bytes)) {
            return $written;
        }

        $bytes = substr($bytes, $written);

        $this->writeDeferred = new Async\Deferred();
        $deferred = &$this->writeDeferred;
        /** @psalm-suppress MissingThrowsDocblock */
        EventLoop::enable($this->writeWatcher);
        $delay_watcher = null;
        if (null !== $timeout) {
            $delay_watcher = EventLoop::delay(
                $timeout,
                static function () use (&$deferred) {
                    /** @var Async\Deferred|null $deferred */
                    $deferred?->error(
                        new Exception\TimeoutException('reached timeout while the handle is still not writable.')
                    );
                }
            );
        }

        try {
            /** @var Async\Deferred $deferred */
            $deferred->getAwaitable()->await();
        } finally {
            $deferred = null;
            EventLoop::disable($this->writeWatcher);
            if (null !== $delay_watcher) {
                EventLoop::cancel($delay_watcher);
            }
        }

        return $written + $this->writeImmediately($bytes);
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function writeImmediately(string $bytes): int
    {
        // there's a pending write operation, wait for it first.
        $this->writeDeferred
            ?->getAwaitable()
            ->then(static fn() => null, static fn() => null)
            ->ignore()
            ->await();

        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fwrite($this->stream, $bytes);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function seek(int $offset): void
    {
        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        Psl\invariant($offset >= 0, '$offset must be a positive-int.');

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fseek($this->stream, $offset);
        if (0 !== $result) {
            throw new Exception\RuntimeException('Failed to seek the specified position.');
        }
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
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

        return $result;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout.
     * @throws InvariantViolationException If $max_bytes is 0, or $timeout is negative.
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        Psl\invariant(
            $timeout === null || $timeout > 0,
            '$timeout must be null, or > 0',
        );

        $chunk = $this->readImmediately($max_bytes);
        if ('' !== $chunk || $this->blocks) {
            return $chunk;
        }

        $this->readDeferred = new Async\Deferred();
        $deferred = &$this->readDeferred;
        /** @psalm-suppress MissingThrowsDocblock */
        EventLoop::enable($this->readWatcher);
        $delay_watcher = null;
        if (null !== $timeout) {
            $delay_watcher = EventLoop::delay(
                $timeout,
                static function () use (&$deferred) {
                    /** @var Async\Deferred|null $deferred */
                    $deferred?->error(
                        new Exception\TimeoutException('reached timeout while the handle is still not readable.')
                    );
                }
            );
        }

        try {
            /** @var Async\Deferred $deferred */
            $deferred->getAwaitable()->await();
        } finally {
            $deferred = null;
            EventLoop::disable($this->readWatcher);
            if (null !== $delay_watcher) {
                EventLoop::cancel($delay_watcher);
            }
        }

        return $this->readImmediately($max_bytes);
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        // there's a pending read operation, wait for it.
        $this->readDeferred
            ?->getAwaitable()
            ->then(static fn() => null, static fn() => null)
            ->ignore()
            ->await()
        ;

        if (!is_resource($this->stream)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        Psl\invariant($max_bytes === null || $max_bytes > 0, '$max_bytes must be null, or > 0');

        if ($max_bytes === null) {
            $max_bytes = self::DEFAULT_READ_BUFFER_SIZE;
        } elseif ($max_bytes > self::MAXIMUM_READ_BUFFER_SIZE) {
            $max_bytes = self::MAXIMUM_READ_BUFFER_SIZE;
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = $this->useSingleRead ? fread($this->stream, $max_bytes) : stream_get_contents($this->stream, $max_bytes);
        if ($result === false) {
            /** @var array{message: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result;
    }

    /**
     * @throws Exception\RuntimeException If unable to close the handle.
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
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

        $this->readDeferred?->error(throw new Exception\AlreadyClosedException('Handle has already been closed.'));
        $this->writeDeferred?->error(throw new Exception\AlreadyClosedException('Handle has already been closed.'));
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
}
