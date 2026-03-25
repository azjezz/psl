<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Psl\HTTP\Message\ProtocolVersion;
use Psl\Socks;
use Psl\TLS;
use Psl\URL\URL;

/**
 * Client-level configuration governing transport behavior, protocol preferences, and response limits.
 *
 * This immutable value object holds all settings that influence how the HTTP client
 * establishes connections, negotiates protocols, and enforces response limits. A default
 * configuration is suitable for most use cases; individual settings can be overridden
 * using the `with*()` methods, which return new instances.
 *
 * The configuration can be provided at two levels:
 *
 * 1. **Client default**: Passed to the {@see Client} constructor and used for all
 *    requests unless overridden.
 * 2. **Per-request override**: Passed to {@see ClientInterface::send()} to override
 *    the client default for a single request.
 *
 * ## Protocol version negotiation
 *
 * The {@see $protocolVersions} list controls which HTTP versions the client is willing
 * to use, ordered from most preferred to least preferred. During TLS handshake, these
 * map to ALPN protocol tokens ("h2" for HTTP/2, "http/1.1" for HTTP/1.1). The server
 * selects the protocol from the advertised list. For plaintext connections, only
 * HTTP/1.x or h2c (HTTP/2 prior knowledge) is possible.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
 * @link https://datatracker.ietf.org/doc/html/rfc9112 HTTP/1.1
 * @link https://datatracker.ietf.org/doc/html/rfc9113 HTTP/2
 *
 * @api
 *
 * @mago-expect lint:excessive-parameter-list
 */
final readonly class ClientConfiguration
{
    /**
     * @param positive-int $maxResponseHeaderSize Maximum total size of response headers in bytes, including all header field names, values, and delimiters.
     *  Responses exceeding this limit trigger a {@see Exception\ProtocolException}. Defaults to 8,192 bytes (8 KiB).
     * @param positive-int $maxResponseBodySize Maximum response body size in bytes. The response body stream will throw a {@see Exception\ProtocolException}
     *  if this limit is exceeded during reading. Defaults to 10,485,760 bytes (10 MiB).
     * @param null|URL $baseUrl Default base URL for resolving relative request targets. When a {@see \Psl\HTTP\Message\Request} has no URL set but has a request target,
     *  it is resolved against this base URL per RFC 3986 Section 5. Defaults to {@see null} (no base URL).
     * @param TLS\ClientConfiguration $tlsConfiguration TLS settings for HTTPS connections, including certificate verification, client certificates,
     *  cipher suites, and minimum protocol version.
     * @param H2ClientConfiguration $h2 HTTP/2-specific session parameters such as flow-control window sizes, maximum frame size,
     *  and concurrent stream limits. These are sent to the server in the SETTINGS frame during
     *  the HTTP/2 connection preface. Only applies when an HTTP/2 connection is established.
     * @param list<ProtocolVersion> $protocolVersions Supported HTTP protocol versions in preference order (most preferred first).
     *  This drives ALPN negotiation during TLS handshake. Defaults to [HTTP/2, HTTP/1.1],
     *  preferring HTTP/2 when the server supports it.
     * @param null|non-empty-string $unixSocket Path to a Unix domain socket to connect through instead of TCP. When set,
     *  DNS resolution and TCP connection are bypassed; the client connects directly to the socket.
     *  Useful for communicating with local services (e.g., Docker daemon). Defaults to {@see null} (use TCP).
     * @param null|Socks\Configuration $proxy SOCKS5 proxy configuration for TCP-level tunneling. When set, all TCP connections are routed
     *  through the SOCKS5 proxy server. The proxy transparently tunnels TCP bytes; TLS, ALPN negotiation, and HTTP framing happen
     *  on top of the tunneled connection unchanged. Defaults to {@see null} (direct TCP connections).
     * @param null|non-empty-string $tunnel HTTP CONNECT tunnel address (e.g., "http://proxy:8080" or "https://proxy:443").
     *  When set, HTTPS connections are established by first connecting to this address and sending an HTTP/1.1 CONNECT request to create a tunnel
     *  to the target host. Plain HTTP connections are sent to the tunnel using absolute-form request targets. Defaults to {@see null} (no tunnel).
     * @param list<non-empty-string> $noTunneling Hosts that bypass the HTTP tunnel and connect directly (or through the SOCKS proxy only).
     *  Supports exact matches ("localhost"), domain suffix matches (".example.com"), and wildcard ("*").
     */
    public function __construct(
        public int $maxResponseHeaderSize = 8_192,
        public int $maxResponseBodySize = 10_485_760,
        public null|URL $baseUrl = null,
        public TLS\ClientConfiguration $tlsConfiguration = new TLS\ClientConfiguration(),
        public H2ClientConfiguration $h2 = new H2ClientConfiguration(),
        public array $protocolVersions = [ProtocolVersion::V20, ProtocolVersion::V11],
        public null|string $unixSocket = null,
        public null|Socks\Configuration $proxy = null,
        public null|string $tunnel = null,
        public array $noTunneling = [],
    ) {}

    /**
     * Return a new configuration with the given maximum response header size.
     *
     * @param positive-int $maxResponseHeaderSize Maximum total size of response headers in bytes.
     */
    public function withMaxResponseHeaderSize(int $maxResponseHeaderSize): self
    {
        return new self(
            $maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given maximum response body size.
     *
     * @param positive-int $maxResponseBodySize Maximum response body size in bytes.
     */
    public function withMaxResponseBodySize(int $maxResponseBodySize): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given base URL for resolving relative request targets.
     */
    public function withBaseUrl(null|URL $baseUrl): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given TLS settings for HTTPS connections.
     */
    public function withTlsConfiguration(TLS\ClientConfiguration $tlsConfiguration): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given HTTP/2 session parameters.
     */
    public function withH2(H2ClientConfiguration $h2): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given protocol version preferences.
     *
     * @param list<ProtocolVersion> $protocolVersions Supported protocol versions in preference order (most preferred first).
     */
    public function withProtocolVersions(array $protocolVersions): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given Unix domain socket path.
     *
     * @param null|non-empty-string $unixSocket Unix socket path to connect through, or {@see null} to use TCP.
     */
    public function withUnixSocket(null|string $unixSocket): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $unixSocket,
            $this->proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given SOCKS5 proxy for TCP-level tunneling.
     */
    public function withProxy(null|Socks\Configuration $proxy): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $proxy,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given HTTP CONNECT tunnel address.
     *
     * @param null|non-empty-string $tunnel Tunnel address (e.g., "http://proxy:8080"), or {@see null} to disable.
     */
    public function withTunnel(null|string $tunnel): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new configuration with the given no-tunneling host list.
     *
     * @param list<non-empty-string> $noTunneling Hosts that bypass the HTTP tunnel.
     */
    public function withNoTunneling(array $noTunneling): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->h2,
            $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $this->tunnel,
            $noTunneling,
        );
    }

    /**
     * Merge per-request overrides into this configuration.
     *
     * Returns a new {@see ClientConfiguration} where each non-null field from
     * the {@see SendConfiguration} replaces the corresponding field in this
     * configuration. Null fields in the send configuration are inherited
     * from this configuration unchanged.
     *
     * Fields that are not overridable per-request ({@see $unixSocket},
     * {@see $proxy}, {@see $h2}) are always inherited from this configuration.
     */
    public function withOverrides(SendConfiguration $overrides): self
    {
        return new self(
            $overrides->maxResponseHeaderSize ?? $this->maxResponseHeaderSize,
            $overrides->maxResponseBodySize ?? $this->maxResponseBodySize,
            $overrides->baseUrl ?? $this->baseUrl,
            $overrides->tlsConfiguration ?? $this->tlsConfiguration,
            $this->h2,
            $overrides->protocolVersions ?? $this->protocolVersions,
            $this->unixSocket,
            $this->proxy,
            $overrides->tunnel ?? $this->tunnel,
            $overrides->noTunneling ?? $this->noTunneling,
        );
    }
}
