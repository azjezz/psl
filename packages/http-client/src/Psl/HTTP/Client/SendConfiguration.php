<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Psl\HTTP\Message\ProtocolVersion;
use Psl\TLS;
use Psl\URL\URL;

/**
 * Per-request configuration overrides for {@see ClientInterface::send()}.
 *
 * Allows callers to override specific transport settings for a single request
 * without affecting the client's default {@see ClientConfiguration}. Each field
 * is nullable: {@see null} means "inherit from the client default", a non-null
 * value overrides it for that request only.
 *
 * The client merges these overrides with its default configuration via
 * {@see ClientConfiguration::withOverrides()} before establishing the connection
 * and performing the exchange.
 *
 * ## What is NOT overridable per-request
 *
 * Connection/session-level concerns that are established once and shared across
 * requests belong exclusively on {@see ClientConfiguration}:
 *
 * - {@see ClientConfiguration::$proxy} - SOCKS5 proxy (TCP-level routing).
 * - {@see ClientConfiguration::$unixSocket} - Unix domain socket transport.
 * - {@see ClientConfiguration::$h2} - HTTP/2 session parameters (SETTINGS frame).
 *
 * These are infrastructure concerns that affect how connections are established
 * and pooled, not how individual requests are handled.
 *
 * ## Usage
 *
 * ```php
 * // Override body size limit for a large download:
 * $tx = $client->send($request, new SendConfiguration(
 *     maxResponseBodySize: 500_000_000,
 * ));
 *
 * // Force HTTP/1.1 for a legacy endpoint:
 * $tx = $client->send($request, new SendConfiguration(
 *     protocolVersions: [ProtocolVersion::V11],
 * ));
 *
 * // Use a different TLS certificate for a specific API:
 * $tx = $client->send($request, new SendConfiguration(
 *     tlsConfiguration: new TLS\ClientConfiguration(certificate: $cert),
 * ));
 * ```
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
 *
 * @api
 */
final readonly class SendConfiguration
{
    /**
     * @param null|positive-int $maxResponseHeaderSize Maximum total size of response headers in bytes. Overrides {@see ClientConfiguration::$maxResponseHeaderSize}. {@see null} inherits the client default.
     * @param null|positive-int $maxResponseBodySize Maximum response body size in bytes. Overrides {@see ClientConfiguration::$maxResponseBodySize}. {@see null} inherits the client default. Increase for large file downloads.
     * @param null|URL $baseUrl Base URL for resolving relative request targets per RFC 3986 Section 5. Overrides {@see ClientConfiguration::$baseUrl}. {@see null} inherits the client default.
     * @param null|TLS\ClientConfiguration $tlsConfiguration TLS settings for this request's HTTPS connection (certificates, verification, cipher suites). Overrides {@see ClientConfiguration::$tlsConfiguration}. {@see null} inherits the client default. Useful when a specific endpoint requires different certificates or peer verification settings.
     * @param null|list<ProtocolVersion> $protocolVersions Protocol version preferences in order (most preferred first). Overrides {@see ClientConfiguration::$protocolVersions} and drives ALPN negotiation. {@see null} inherits the client default. Useful for forcing HTTP/1.1 on endpoints that misbehave with HTTP/2.
     * @param null|non-empty-string $tunnel HTTP CONNECT tunnel address (e.g., "http://proxy:8080"). Overrides {@see ClientConfiguration::$tunnel}. {@see null} inherits the client default.
     * @param null|list<non-empty-string> $noTunneling Hosts that bypass the HTTP tunnel. Overrides {@see ClientConfiguration::$noTunneling}. {@see null} inherits the client default.
     */
    public function __construct(
        public null|int $maxResponseHeaderSize = null,
        public null|int $maxResponseBodySize = null,
        public null|URL $baseUrl = null,
        public null|TLS\ClientConfiguration $tlsConfiguration = null,
        public null|array $protocolVersions = null,
        public null|string $tunnel = null,
        public null|array $noTunneling = null,
    ) {}

    /**
     * Return a new send configuration with the given maximum response header size.
     *
     * @param null|positive-int $maxResponseHeaderSize Maximum response header size in bytes, or {@see null} to inherit.
     */
    public function withMaxResponseHeaderSize(null|int $maxResponseHeaderSize): self
    {
        return new self(
            $maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given maximum response body size.
     *
     * @param null|positive-int $maxResponseBodySize Maximum response body size in bytes, or {@see null} to inherit.
     */
    public function withMaxResponseBodySize(null|int $maxResponseBodySize): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given base URL.
     *
     * @param null|URL $baseUrl Base URL for relative request target resolution, or {@see null} to inherit.
     */
    public function withBaseUrl(null|URL $baseUrl): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given TLS settings.
     *
     * @param null|TLS\ClientConfiguration $tlsConfiguration TLS settings for this request, or {@see null} to inherit.
     */
    public function withTlsConfiguration(null|TLS\ClientConfiguration $tlsConfiguration): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $tlsConfiguration,
            $this->protocolVersions,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given protocol version preferences.
     *
     * @param null|list<ProtocolVersion> $protocolVersions Protocol versions in preference order, or {@see null} to inherit.
     */
    public function withProtocolVersions(null|array $protocolVersions): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $protocolVersions,
            $this->tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given HTTP CONNECT tunnel address.
     *
     * @param null|non-empty-string $tunnel Tunnel address (e.g., "http://proxy:8080"), or {@see null} to inherit.
     */
    public function withTunnel(null|string $tunnel): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $tunnel,
            $this->noTunneling,
        );
    }

    /**
     * Return a new send configuration with the given no-tunneling host list.
     *
     * @param null|list<non-empty-string> $noTunneling Hosts that bypass the tunnel, or {@see null} to inherit.
     */
    public function withNoTunneling(null|array $noTunneling): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->tunnel,
            $noTunneling,
        );
    }
}
