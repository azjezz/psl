<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Asio;
use Psl\Exception\InvariantViolationException;
use Psl\Internal;
use Psl\IO\CloseSeekReadWriteHandleInterface;
use Psl\IO\Exception;
use Psl\Type;

use function error_clear_last;
use function error_get_last;
use function fclose;
use function fseek;
use function ftell;
use function fwrite;
use function stream_get_contents;
use function stream_get_meta_data;
use function stream_set_blocking;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
class ResourceHandle implements CloseSeekReadWriteHandleInterface
{
    protected const DEFAULT_READ_BUFFER_SIZE = 1024 * 8;

    use ReadHandleConvenienceMethodsTrait;
    use WriteHandleConvenienceMethodsTrait;

    /**
     * @var closed-resource|resource|null $resource
     */
    private $resource;

    /**
     * @param resource $resource
     *
     * @throws Type\Exception\AssertException If $resource is not a resource.
     * @throws Exception\BlockingException If unable to set the handle resource to non-blocking mode.
     */
    public function __construct($resource)
    {
        $this->resource = Type\resource()->assert($resource);

        /** @psalm-suppress MissingThrowsDocblock */
        $this->box(static function () use ($resource): void {
            $result = stream_set_blocking($resource, false);
            if ($result === false) {
                $error = error_get_last();

                throw new Exception\BlockingException(
                    $error['message'] ?? 'Unable to set the handle resource to non-blocking mode'
                );
            }
        });
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout.
     */
    protected function select(int $flags, ?int $timeout): void
    {
        $result = $this->box(
            /**
             * @param resource $resource
             */
            static fn ($resource): int => (int) Asio\await(
                Asio\Internal\stream_await($resource, $flags, $timeout)
            )
        );

        if (Asio\Internal\STREAM_AWAIT_ERROR === $result) {
            throw new Exception\RuntimeException('select_await() failed.');
        }

        if (Asio\Internal\STREAM_AWAIT_TIMEOUT === $result) {
            throw new Exception\TimeoutException('reached timeout while the handle is still not ready.');
        }
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function writeImmediately(string $bytes): int
    {
        return (int) $this->box(
            /**
             * @param resource $resource
             */
            static function ($resource) use ($bytes) {
                $metadata = stream_get_meta_data($resource);
                if ($metadata['blocked']) {
                    throw new Exception\BlockingException('The handle resource is blocking.');
                }

                $result = fwrite($resource, $bytes);
                if ($result === false) {
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $timeout_ms is negative.
     */
    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        Psl\invariant(
            $timeout_ms === null || $timeout_ms > 0,
            '$timeout_ms must be null, or > 0',
        );

        try {
            return $this->writeImmediately($bytes);
        } catch (Exception\BlockingException $_ex) {
            // We need to wait, which we do below...
        }

        $this->select(Asio\Internal\STREAM_AWAIT_WRITE, $timeout_ms);

        return $this->writeImmediately($bytes);
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function seek(int $offset): void
    {
        $this->box(
            /**
             * @param resource $resource
             */
            static function ($resource) use ($offset) {
                $metadata = stream_get_meta_data($resource);
                Psl\invariant($metadata['seekable'], 'Stream is not seekable.');

                $result = fseek($resource, $offset);
                if (0 !== $result) {
                    throw new Exception\RuntimeException('Failed to seek the specified position.');
                }
            }
        );
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function tell(): int
    {
        return (int) $this->box(
            /**
             * @param resource $resource
             */
            static function ($resource) {
                $metadata = stream_get_meta_data($resource);
                Psl\invariant($metadata['seekable'], 'Stream is not seekable.');

                $result = ftell($resource);
                if ($result === false) {
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        return (string) $this->box(
            /**
             * @param resource $resource
             */
            static function ($resource) use ($max_bytes) {
                Psl\invariant($max_bytes === null || $max_bytes > 0, '$max_bytes must be null, or > 0');
                $metadata = stream_get_meta_data($resource);
                if ($metadata['blocked']) {
                    throw new Exception\BlockingException('The handle resource is blocking.');
                }

                $max_bytes = $max_bytes ?? self::DEFAULT_READ_BUFFER_SIZE;
                $result = fread($resource, $max_bytes);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout.
     * @throws InvariantViolationException If $max_bytes is 0, or $timeout_ms is negative.
     */
    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        Psl\invariant(
            $timeout_ms === null || $timeout_ms > 0,
            '$timeout_ms must be null, or > 0',
        );

        $chunk = $this->readImmediately($max_bytes);
        if ('' !== $chunk) {
            return $chunk;
        }

        $this->select(Asio\Internal\STREAM_AWAIT_READ, $timeout_ms);

        return $this->readImmediately($max_bytes);
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If unable to close the handle.
     */
    public function close(): void
    {
        $this->box(
            /**
             * @param resource $resource
             */
            function ($resource) {
                $result = fclose($resource);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                $this->resource = null;
            }
        );
    }

    /**
     * @template T
     *
     * @param (callable(resource): T) $fun
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return T
     */
    public function box(callable $fun)
    {
        error_clear_last();

        $resource = $this->resource;
        if (!Type\resource()->matches($resource)) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        return Internal\suppress(static fn () => $fun($resource));
    }
}
