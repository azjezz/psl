<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

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
    public function __construct($stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: true, seek: false, close: true);
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(?int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }

    /**
     * {@inheritDoc}
     */
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
