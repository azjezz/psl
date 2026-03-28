<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Closure;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Response;
use Psl\TLS;
use Psl\URL\URL;

/**
 * Per-request configuration for {@see ClientInterface::send()}.
 *
 * Combines two concerns:
 *
 * 1. **Transport overrides**: nullable fields that override the client's default
 *    {@see ClientConfiguration} for a single request. {@see null} means "inherit
 *    from the client default"; a non-null value overrides it for that request only.
 *    The client merges these overrides via {@see ClientConfiguration::withOverrides()}
 *    before establishing the connection and performing the exchange.
 *
 * 2. **Per-request callbacks**: event callbacks that are specific to a single
 *    exchange and have no corresponding {@see ClientConfiguration} field. These
 *    include {@see $onConnection} (fired when a connection is acquired) and
 *    {@see $onInformationalResponse} (fired for each 1xx response).
 *
 * ## What is NOT overridable per-request
 *
 * Connection/session-level concerns that are established once and shared across
 * requests belong exclusively on {@see ClientConfiguration}:
 *
 * - {@see ClientConfiguration::$socksConfiguration} - SOCKS5 proxy (TCP-level routing).
 * - {@see ClientConfiguration::$unixSocket} - Unix domain socket transport.
 * - {@see ClientConfiguration::$h2ClientConfiguration} - HTTP/2 session parameters (SETTINGS frame).
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
 * // Capture connection metadata when a connection is acquired:
 * $tx = $client->send($request, new SendConfiguration(
 *     onConnection: function (ConnectionMetadata $metadata): void {
 *         echo $metadata->peerAddress->host;
 *     },
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
     * @param null|positive-int $maxResponseHeaderSize Maximum total size of response headers in bytes. Overrides {@see ClientConfiguration::$maxResponseHeaderSize}.
     *  {@see null} inherits the client default.
     * @param null|positive-int $maxResponseBodySize Maximum response body size in bytes. Overrides {@see ClientConfiguration::$maxResponseBodySize}.
     *  {@see null} inherits the client default. Increase for large file downloads.
     * @param null|URL $baseUrl Base URL for resolving relative request targets per RFC 3986 Section 5. Overrides {@see ClientConfiguration::$baseUrl}.
     *  {@see null} inherits the client default.
     * @param null|TLS\ClientConfiguration $tlsConfiguration TLS settings for this request's HTTPS connection (certificates, verification, cipher suites).
     *  Overrides {@see ClientConfiguration::$tlsConfiguration}. {@see null} inherits the client default. Useful when a specific endpoint requires different certificates or peer verification settings.
     * @param null|list<ProtocolVersion> $protocolVersions Protocol version preferences in order (most preferred first). Overrides {@see ClientConfiguration::$protocolVersions}
     *  and drives ALPN negotiation. {@see null} inherits the client default. Useful for forcing HTTP/1.1 on endpoints that misbehave with HTTP/2.
     * @param null|ProxyConfiguration $proxyConfiguration HTTP proxy configuration. Overrides {@see ClientConfiguration::$proxy}.
     *  {@see null} inherits the client default.
     * @param null|(Closure(Response): void) $onInformationalResponse Callback invoked for each informational (1xx) response received during the exchange.
     *  Called with the informational {@see Response} as it arrives, before the final response. When both this callback and the
     *  client-level {@see ClientConfiguration::$onInformationalResponse} callback are set, both are invoked (client-level first,
     *  then this per-request callback). {@see null} disables the per-request callback (the client-level callback, if any, still fires).
     * @param null|(Closure(ConnectionMetadata): void) $onConnection Callback invoked when a connection is acquired for the exchange. Called with
     *  the connection metadata immediately after the connection is obtained from the connector (which may be a fresh or reused connection).
     *  {@see null} disables the callback.
     * @param null|Duration $connectionTimeout Maximum duration for establishing the connection (TCP + TLS handshake).
     *  When set, the connector's {@see CancellationTokenInterface} is linked with a {@see TimeoutCancellationToken} scoped
     *  to this duration, so the connection attempt is cancelled if it exceeds the limit. The overall request cancellation
     *  token still applies independently. {@see null} means no connection-specific timeout (only the request-level
     *  cancellation token governs the connection phase).
     */
    public function __construct(
        public null|int $maxResponseHeaderSize = null,
        public null|int $maxResponseBodySize = null,
        public null|URL $baseUrl = null,
        public null|TLS\ClientConfiguration $tlsConfiguration = null,
        public null|array $protocolVersions = null,
        public null|ProxyConfiguration $proxyConfiguration = null,
        public null|Closure $onInformationalResponse = null,
        public null|Closure $onConnection = null,
        public null|Duration $connectionTimeout = null,
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
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
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
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
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
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
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
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
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
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
        );
    }

    /**
     * Return a new send configuration with the given HTTP proxy configuration.
     */
    public function withProxyConfiguration(null|ProxyConfiguration $proxyConfiguration): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
        );
    }

    /**
     * Return a new send configuration with the given informational response callback.
     *
     * @param null|(Closure(Response): void) $onInformationalResponse Callback for 1xx responses, or {@see null} to disable.
     */
    public function withOnInformationalResponse(null|Closure $onInformationalResponse): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->proxyConfiguration,
            $onInformationalResponse,
            $this->onConnection,
            $this->connectionTimeout,
        );
    }

    /**
     * Return a new send configuration with the given connection callback.
     *
     * @param null|(Closure(ConnectionMetadata): void) $onConnection Callback for connection acquisition, or {@see null} to disable.
     */
    public function withOnConnection(null|Closure $onConnection): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $onConnection,
            $this->connectionTimeout,
        );
    }

    /**
     * Return a new send configuration with the given connection timeout.
     *
     * @param null|Duration $connectionTimeout Maximum duration for establishing the connection, or {@see null} for no connection-specific timeout.
     */
    public function withConnectionTimeout(null|Duration $connectionTimeout): self
    {
        return new self(
            $this->maxResponseHeaderSize,
            $this->maxResponseBodySize,
            $this->baseUrl,
            $this->tlsConfiguration,
            $this->protocolVersions,
            $this->proxyConfiguration,
            $this->onInformationalResponse,
            $this->onConnection,
            $connectionTimeout,
        );
    }
}
