<?php

declare(strict_types=1);

namespace Psl\Network;

final class Address
{
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 0;

    private function __construct(
        public readonly SocketScheme $scheme,
        public readonly string $host,
        public readonly ?int $port = null,
    ) {
    }

    public static function create(SocketScheme $scheme, string $host, ?int $port = null): self
    {
        return new self($scheme, $host, $port);
    }

    public static function unix(string $host): self
    {
        return new self(SocketScheme::UNIX, $host, null);
    }

    public static function tcp(string $host = self::DEFAULT_HOST, int $port = self::DEFAULT_PORT): self
    {
        return new self(SocketScheme::TCP, $host, $port);
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        $address = "{$this->scheme->value}://{$this->host}";

        if (null === $this->port) {
            return $address;
        }

        return "{$address}:{$this->port}";
    }
}
