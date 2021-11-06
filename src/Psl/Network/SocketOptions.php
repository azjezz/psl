<?php

declare(strict_types=1);

namespace Psl\Network;

final class SocketOptions
{
    public function __construct(
        public readonly bool $addressReuse,
        public readonly bool $portReuse,
        public readonly bool $broadcast,
    ) {
    }

    public static function create(
        bool $addressReuse = false,
        bool $portReuse = false,
        bool $broadcast = false,
    ): SocketOptions {
        return new self($addressReuse, $portReuse, $broadcast);
    }

    public function withAddressReuse(bool $enabled = true): SocketOptions
    {
        return new self($enabled, $this->portReuse, $this->broadcast);
    }

    public function withPortReuse(bool $enabled = true): SocketOptions
    {
        return new self($this->addressReuse, $enabled, $this->broadcast);
    }

    public function withBroadcast(bool $enabled = true): SocketOptions
    {
        return new self($this->addressReuse, $this->portReuse, $enabled);
    }
}
