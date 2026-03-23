<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

use function pack;

/**
 * EDNS TCP Keepalive option (RFC 7828, option code 11).
 *
 * Signals TCP keepalive support and optionally specifies
 * an idle timeout for persistent TCP connections.
 *
 * @api
 */
final class TCPKeepaliveOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 11;
    }

    /**
     * @param null|int<0, 65535> $timeout Idle timeout in 100ms units; null = no timeout (query mode).
     */
    public function __construct(
        public readonly null|int $timeout = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        if ($this->timeout === null) {
            return '';
        }

        return pack('n', $this->timeout);
    }
}
