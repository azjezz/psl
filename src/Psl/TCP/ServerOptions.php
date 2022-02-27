<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Network;

final class ServerOptions
{
    public const DEFAULT_IDLE_CONNECTIONS = 256;

    /**
     * @param int<1, max> $idleConnections
     */
    public function __construct(
        public readonly bool $noDelay,
        public readonly int $idleConnections,
        public readonly Network\SocketOptions $socketOptions,
    ) {
    }

    /**
     * @param int<1, max> $idleConnections
     */
    public static function create(
        bool $noDelay = false,
        int $idleConnections = self::DEFAULT_IDLE_CONNECTIONS,
        ?Network\SocketOptions $socketOptions = null,
    ): ServerOptions {
        return new self($noDelay, $idleConnections, $socketOptions ?? Network\SocketOptions::create());
    }

    public function withSocketOptions(
        Network\SocketOptions $socketOptions
    ): ServerOptions {
        return new self($this->noDelay, $this->idleConnections, $socketOptions);
    }

    public function withNoDelay(bool $enabled = true): ServerOptions
    {
        return new self($enabled, $this->idleConnections, $this->socketOptions);
    }

    /**
     * @param int<1, max> $idleConnections
     */
    public function withIdleConnections(int $idleConnections): ServerOptions
    {
        return new self($this->noDelay, $idleConnections, $this->socketOptions);
    }
}
