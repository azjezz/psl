<?php

declare(strict_types=1);

namespace Psl\Unix\Internal;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\IO\Exception;
use Psl\IO\Internal\ResourceHandle;
use Psl\Network;
use Psl\Network\Address;
use Psl\Unix;
use Revolt\EventLoop;
use Socket as PHPSocket;

use function is_resource;
use function stream_socket_recvfrom;
use function stream_socket_shutdown;

use const STREAM_PEEK;
use const STREAM_SHUT_WR;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Stream implements Unix\StreamInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;
    use IO\ReadHandleConvenienceMethodsTrait;

    private ResourceHandle $handle;

    /**
     * @param resource|PHPSocket $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new ResourceHandle($stream, read: true, write: true, seek: false, close: true);
    }

    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
    }

    /**
     * @return resource|object|null
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }

    #[Override]
    public function getLocalAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        return Network\Internal\get_sock_name($stream);
    }

    #[Override]
    public function getPeerAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        return Network\Internal\get_peer_name($stream);
    }

    /**
     * @param positive-int $max_bytes
     */
    #[Override]
    public function peek(int $max_bytes, null|Duration $timeout = null): string
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        if ($timeout !== null) {
            $suspension = EventLoop::getSuspension();
            $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use (
                $suspension,
            ): void {
                $suspension->resume(true);
            });

            $read_watcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
                EventLoop::cancel($watcher);
                $suspension->resume(false);
            });

            /** @var bool $timed_out */
            $timed_out = $suspension->suspend();
            EventLoop::cancel($timeout_watcher);
            EventLoop::cancel($read_watcher);

            if ($timed_out) {
                throw new IO\Exception\TimeoutException('Peek operation timed out.');
            }
        }

        $data = @stream_socket_recvfrom($stream, $max_bytes, STREAM_PEEK);
        if ($data === false) {
            throw new IO\Exception\RuntimeException('Failed to peek data from stream.');
        }

        return $data;
    }

    #[Override]
    public function shutdown(): void
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        $result = @stream_socket_shutdown($stream, STREAM_SHUT_WR);
        if ($result === false) {
            throw new IO\Exception\RuntimeException('Failed to shut down the write side of the stream.');
        }
    }

    #[Override]
    public function close(): void
    {
        $this->handle->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}
