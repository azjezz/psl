<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\DateTime\Duration;
use Psl\IO;
use Psl\IO\Exception;
use Psl\IO\Internal;
use Psl\Network;
use Psl\Network\Address;

use function is_resource;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Socket implements Network\StreamSocketInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;
    use IO\ReadHandleConvenienceMethodsTrait;

    private Internal\ResourceHandle $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: true, seek: false, close: true);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getLocalAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Socket handle has already been closed.');
        }

        return Network\Internal\get_sock_name($stream);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getPeerAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Socket handle has already been closed.');
        }

        return Network\Internal\get_peer_name($stream);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function close(): void
    {
        $this->handle->close();
    }

    public function __destruct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $this->close();
    }
}
