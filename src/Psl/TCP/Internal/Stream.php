<?php

declare(strict_types=1);

namespace Psl\TCP\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\IO\Exception;
use Psl\IO\Internal\ResourceHandle;
use Psl\Network;
use Psl\Network\Address;
use Psl\TCP;
use Revolt\EventLoop;

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
final class Stream implements TCP\StreamInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;
    use IO\ReadHandleConvenienceMethodsTrait;

    private ResourceHandle $handle;

    /**
     * @param resource $stream
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
    public function read(
        null|int $max_bytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->handle->read($max_bytes, $cancellation);
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
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->handle->write($bytes, $cancellation);
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
    public function peek(int $max_bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        $cancellation->throwIfCancelled();

        $suspension = EventLoop::getSuspension();

        $cancellation_id = $cancellation->subscribe($suspension->throw(...));

        $read_watcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(null);
        });

        try {
            $suspension->suspend();
        } finally {
            EventLoop::cancel($read_watcher);
            $cancellation->unsubscribe($cancellation_id);
        }

        /** @psalm-suppress MissingThrowsDocblock */
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
