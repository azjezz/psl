<?php

declare(strict_types=1);

namespace Psl\Network;

/**
 * @psalm-immutable
 */
final readonly class Address
{
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 0;

    public SocketScheme $scheme;

    /**
     * @var non-empty-string
     */
    public string $host;

    /**
     * @var int<0, 65535>|null
     */
    public null|int $port;

    /**
     * @param SocketScheme $scheme
     * @param non-empty-string $host
     * @param int<0,65535>|null $port
     *
     * @psalm-mutation-free
     */
    private function __construct(SocketScheme $scheme, string $host, null|int $port)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param SocketScheme $scheme
     * @param non-empty-string $host
     * @param int<0,65535>|null $port
     *
     * @pure
     */
    public static function create(SocketScheme $scheme, string $host, null|int $port = null): self
    {
        return new self($scheme, $host, $port);
    }

    /**
     * @param non-empty-string $host
     *
     * @pure
     */
    public static function unix(string $host): self
    {
        return new self(SocketScheme::Unix, $host, null);
    }

    /**
     * @param non-empty-string $host
     * @param int<0,65535> $port
     *
     * @pure
     */
    public static function tcp(string $host = self::DEFAULT_HOST, int $port = self::DEFAULT_PORT): self
    {
        return new self(SocketScheme::Tcp, $host, $port);
    }

    /**
     * @return non-empty-string
     *
     * @psalm-mutation-free
     */
    public function toString(): string
    {
        $address = "{$this->scheme->value}://$this->host";
        if (null === $this->port) {
            return $address;
        }

        return "$address:$this->port";
    }
}
