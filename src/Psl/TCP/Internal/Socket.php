<?php

declare(strict_types=1);

namespace Psl\TCP\Internal;

use Psl\IO\Exception;
use Psl\IO\Internal;
use Psl\Network;
use Psl\Network\Address;
use Psl\TCP;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Socket extends Internal\ResourceHandle implements TCP\SocketInterface
{
    /**
     * @param resource $stream
     */
    public function __construct($stream)
    {
        parent::__construct($stream, read: true, write: true, seek: false);
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalAddress(): Address
    {
        if (null === $this->stream) {
            throw new Exception\AlreadyClosedException('Socket handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        return Network\Internal\get_sock_name($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function getPeerAddress(): Address
    {
        if (null === $this->stream) {
            throw new Exception\AlreadyClosedException('Socket handle has already been closed.');
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        return Network\Internal\get_peer_name($this->stream);
    }
}
