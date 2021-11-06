<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Network;

final class ServerOptions
{
    public function __construct(
        public readonly bool $noDelay,
        public readonly Network\SocketOptions $socketOptions,
    ) {
    }

    public static function create(
        bool $noDelay = false,
        ?Network\SocketOptions $socketOptions = null,
    ): ServerOptions {
        return new self($noDelay, $socketOptions ?? Network\SocketOptions::create());
    }

    public function withSocketOptions(
        Network\SocketOptions $socketOptions
    ): ServerOptions {
        return new self($this->noDelay, $socketOptions);
    }

    public function withNoDelay(bool $enabled = true): ServerOptions
    {
        return new self($enabled, $this->socketOptions);
    }
}
