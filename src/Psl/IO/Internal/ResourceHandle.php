<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Async;
use Psl\Exception\InvariantViolationException;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;

use function error_get_last;
use function fclose;
use function fseek;
use function ftell;
use function fwrite;
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
class ResourceHandle implements IO\CloseSeekReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    public const DEFAULT_READ_BUFFER_SIZE = 65536;
    public const MAXIMUM_READ_BUFFER_SIZE = 786432;

    /**
     * @var object|resource|null $resource
     */
    protected mixed $resource;

    private bool $useSingleRead;

    private bool $blocks;

    /**
     * @param resource|object $resource
     *
     * @throws Exception\BlockingException If unable to set the handle resource to non-blocking mode.
     */
    public function __construct(mixed $resource, bool $read, bool $write, bool $seek)
    {
        $this->resource = Type\union(
            Type\resource('stream'),
            Type\object(),
        )->assert($resource);

        /** @psalm-suppress UnusedFunctionCall */
        stream_set_read_buffer($resource, 0);
        $result = stream_set_blocking($resource, false);
        if ($result === false) {
            $error = error_get_last();

            throw new Exception\BlockingException(
                $error['message'] ?? 'Unable to set the handle resource to non-blocking mode'
            );
        }

        $meta = stream_get_meta_data($resource);
        $this->blocks = ($meta['wrapper_type'] ?? '') === 'plainfile';
        if ($seek) {
            $seekable = (bool) $meta['seekable'];

            Psl\invariant($seekable, 'Handle is not seekable.');
        }

        if ($read) {
            $readable = str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');

            Psl\invariant($readable, 'Handle is not readable.');
        }

        if ($write) {
            $writable = str_contains($meta['mode'], 'x')
                || str_contains($meta['mode'], 'w')
                || str_contains($meta['mode'], 'c')
                || str_contains($meta['mode'], 'a')
                || str_contains($meta['mode'], '+')
            ;

            Psl\invariant($writable, 'Handle is not writeable.');
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

        try {
            /**
             * @psalm-suppress PossiblyInvalidArgument
             * @psalm-suppress PossiblyNullArgument - If null, writeImmediately would have thrown.
             */
            Async\await_writable($this->resource, timeout: $timeout);
        } catch (Async\Exception\TimeoutException) {
            throw new Exception\TimeoutException('reached timeout while the handle is still not writable.');
        } catch (Async\Exception\ResourceClosedException) {
            $this->resource = null;

            throw new Exception\AlreadyClosedException('Handle has already been closed.');
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
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fwrite($this->resource, $bytes);
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
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        Psl\invariant($offset >= 0, '$offset must be a positive-int.');

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fseek($this->resource, $offset);
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
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @ftell($this->resource);
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

        try {
            /**
             * @psalm-suppress PossiblyInvalidArgument
             * @psalm-suppress PossiblyNullArgument - If null, writeImmediately would have thrown.
             */
            Async\await_readable($this->resource, timeout: $timeout);
        } catch (Async\Exception\TimeoutException) {
            throw new Exception\TimeoutException('reached timeout while the handle is still not readable.');
        } catch (Async\Exception\ResourceClosedException) {
            $this->resource = null;

            throw new Exception\AlreadyClosedException('Handle has already been closed.');
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
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        Psl\invariant($max_bytes === null || $max_bytes > 0, '$max_bytes must be null, or > 0');

        if ($max_bytes === null) {
            $max_bytes = self::DEFAULT_READ_BUFFER_SIZE;
        } elseif ($max_bytes > self::MAXIMUM_READ_BUFFER_SIZE) {
            $max_bytes = self::MAXIMUM_READ_BUFFER_SIZE;
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = $this->useSingleRead ? fread($this->resource, $max_bytes) : stream_get_contents($this->resource, $max_bytes);
        if ($result === false) {
            /** @var array{message: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        return $result;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If unable to close the handle.
     */
    public function close(): void
    {
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $resource = $this->resource;
        $this->resource = null;
        $result = @fclose($resource);
        if ($result === false) {
            /** @var array{message: string} $error */
            $error = error_get_last();

            throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }
    }
}
