<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Internal;
use Psl\IO\CloseSeekReadWriteHandleInterface;
use Psl\Type;

use function error_clear_last;
use function error_get_last;
use function fclose;
use function fflush;
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
final class ResourceHandle implements CloseSeekReadWriteHandleInterface
{
    /**
     * @var closed-resource|resource|null $resource
     */
    private $resource;

    /**
     * @param resource $resource
     *
     * @throws Type\Exception\AssertException If $resource is not a resource.
     * @throws Psl\IO\Exception\BlockingException If unable to set the handle resource to non-blocking mode.
     */
    public function __construct(mixed $resource)
    {
        $this->resource = Type\resource()->assert($resource);

        /** @psalm-suppress MissingThrowsDocblock */
        $this->box(static function () use ($resource): void {
            $result = stream_set_blocking($resource, false);
            if ($result === false) {
                $error = error_get_last();

                throw new Psl\IO\Exception\BlockingException(
                    $error['message'] ?? 'Unable to set the handle resource to non-blocking mode'
                );
            }
        });
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Psl\IO\Exception\RuntimeException If an error occurred during the operation.
     */
    public function write(string $bytes): int
    {
        return $this->box(
            /**
             * @param resource $resource
             */
            static function (mixed $resource) use ($bytes) {
                $metadata = stream_get_meta_data($resource);
                if ($metadata['blocked']) {
                    throw new Psl\IO\Exception\BlockingException('The handle resource is blocking.');
                }

                $result = fwrite($resource, $bytes);
                if ($result === false) {
                    $error = error_get_last();

                    throw new Psl\IO\Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\RuntimeException If an error occurred during the operation.
     */
    public function seek(int $offset): void
    {
        $this->box(
            /**
             * @param resource $resource
             */
            static function (mixed $resource) use ($offset) {
                $metadata = stream_get_meta_data($resource);
                Psl\invariant($metadata['seekable'], 'Stream is not seekable.');

                $result = fseek($resource, $offset);
                if (0 !== $result) {
                    throw new Psl\IO\Exception\RuntimeException('Failed to seek the specified position.');
                }
            }
        );
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\RuntimeException If an error occurred during the operation.
     */
    public function tell(): int
    {
        return $this->box(
            /**
             * @param resource $resource
             */
            static function (mixed $resource): int {
                $metadata = stream_get_meta_data($resource);
                Psl\invariant($metadata['seekable'], 'Stream is not seekable.');

                $result = ftell($resource);
                if ($result === false) {
                    $error = error_get_last();

                    throw new Psl\IO\Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Psl\IO\Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     */
    public function read(?int $max_bytes = null): string
    {
        return $this->box(
            /**
             * @param resource $resource
             */
            static function (mixed $resource) use ($max_bytes): string {
                Psl\invariant($max_bytes === null || $max_bytes > 0, '$max_bytes must be null, or > 0');
                $metadata = stream_get_meta_data($resource);
                if ($metadata['blocked']) {
                    throw new Psl\IO\Exception\BlockingException('The handle resource is blocking.');
                }

                if (null !== $max_bytes && $metadata['unread_bytes'] < $max_bytes) {
                    $max_bytes = $metadata['unread_bytes'];
                }

                $result = stream_get_contents($resource, $max_bytes ?? -1);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Psl\IO\Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }

                return $result;
            }
        );
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\RuntimeException If unable to flush the handle.
     */
    public function flush(): void
    {
        $this->box(
            /**
             * @param resource $resource
             */
            static function (mixed $resource): void {
                $result = fflush($resource);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Psl\IO\Exception\RuntimeException($error['message'] ?? 'unknown error.');
                }
            }
        );
    }

    /**
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Psl\IO\Exception\RuntimeException If unable to close the handle.
     */
    public function close(): void
    {
        $this->box(
            /**
             * @param resource $resource
             */
            function (mixed $resource): void {
                $result = fclose($resource);
                if ($result === false) {
                    /** @var array{message: string} $error */
                    $error = error_get_last();

                    throw new Psl\IO\Exception\RuntimeException($error['message'] ?? 'unknown error.');
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
     * @throws Psl\IO\Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return T
     */
    public function box(callable $fun)
    {
        error_clear_last();

        $resource = $this->resource;
        if (!Type\resource()->matches($resource)) {
            throw new Psl\IO\Exception\AlreadyClosedException('Handle has already been closed.');
        }

        return Internal\suppress(static fn() => $fun($resource));
    }
}
