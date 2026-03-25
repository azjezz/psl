<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Psl\URL\Internal;
use Psl\URL\URL;
use Stringable;

/**
 * Represents a connection origin (scheme + host + port) for connection pooling.
 *
 * Two requests share a connection pool entry if and only if they have the same
 * origin. The origin is derived from the request URL's scheme, host, and port
 * (with default ports applied per the scheme).
 *
 * @internal
 */
final readonly class Origin implements Stringable
{
    /**
     * @param non-empty-string $scheme The URL scheme (e.g., "https", "http").
     * @param non-empty-string $host The hostname or IP address.
     * @param int<1, 65535> $port The port number (default port applied if omitted from the URL).
     */
    public function __construct(
        public string $scheme,
        public string $host,
        public int $port,
    ) {}

    /**
     * Derive the origin from a parsed URL, applying the default port for the scheme.
     *
     * @param URL $url The parsed URL.
     *
     * @return self The origin for connection pooling.
     */
    public static function fromUrl(URL $url): self
    {
        /** @var int<1, 65535> $port */
        $port = $url->authority->port ?? Internal\default_port($url->scheme) ?? 80;

        return new self($url->scheme, $url->authority->host->toString(), $port);
    }

    /**
     * Return the origin as "scheme://host:port".
     */
    public function toString(): string
    {
        return $this->scheme . '://' . $this->host . ':' . $this->port;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
