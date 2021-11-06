<?php

declare(strict_types=1);

namespace Psl\TCP;

final class ConnectOptions
{
    public function __construct(
        public readonly bool $noDelay,
    ) {
    }

    public static function create(
        bool $noDelay = false,
    ): ConnectOptions {
        return new self($noDelay);
    }

    public function withNoDelay(bool $enabled = true): ConnectOptions
    {
        return new self($enabled);
    }
}
