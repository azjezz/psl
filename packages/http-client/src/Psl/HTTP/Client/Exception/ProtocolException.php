<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\HTTP\Client;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\IO;

/**
 * Thrown when the server sends a malformed or unexpected response, when a
 * requested protocol version is not supported, or when a response body
 * exceeds the configured size limit.
 *
 * This exception covers four categories of protocol-level failures:
 *
 * 1. **Malformed responses**: The server sent a response that violates HTTP
 *    message syntax. This includes invalid status lines, malformed headers,
 *    invalid chunk encoding, bad redirect URLs, and missing HTTP/2
 *    pseudo-headers. Thrown by the HTTP/1.x response reader
 *    ({@see Client\Internal\H1\ResponseReader}), chunked and
 *    fixed-length body handles, the HTTP/2 stream layer
 *    ({@see Client\Internal\H2\H2Stream}), the HTTP/2 multiplexer
 *    ({@see Client\Internal\H2\H2Multiplexer}), and the redirect
 *    client ({@see Client\RedirectClient}).
 *
 * 2. **Unsupported protocol versions**: The request specifies a protocol
 *    version that is not present in the client configuration, or the
 *    resolved version is not supported by the connector implementation.
 *    Thrown during protocol version resolution
 *    ({@see Client\Internal\resolve_protocol_versions()}).
 *
 * 3. **Premature stream termination**: The connection was closed before
 *    the response was fully received. Thrown by the HTTP/1.x response
 *    reader and the HTTP tunnel ({@see Client\Internal\HttpTunnel}).
 *
 * 4. **Response body size violations**: The response body exceeds the
 *    maximum size configured via {@see Client\ClientConfiguration}.
 *    Thrown by the body size limiter
 *    ({@see IO\BoundedReadHandle}) and the HTTP/2
 *    response body handle
 *    ({@see Client\Internal\H2\ResponseBodyHandle}).
 *
 * @see RuntimeException Parent exception for all HTTP client errors.
 * @see RequestException For errors originating from the request itself.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9112 HTTP/1.1 Message Syntax
 * @link https://datatracker.ietf.org/doc/html/rfc9113 HTTP/2
 *
 * @api
 */
final class ProtocolException extends RuntimeException
{
    /**
     * Create an exception for a response that violates HTTP message syntax.
     *
     * Used when the server sends an unparseable or structurally invalid
     * response, including invalid status lines, malformed headers, bad chunk
     * encoding, invalid redirect locations, HTTP tunnel failures, and missing
     * HTTP/2 pseudo-headers.
     *
     * @param string $detail Human-readable description of the malformation.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9112#section-2.1 Message Format
     */
    public static function forMalformedResponse(string $detail): self
    {
        return new self('Malformed HTTP response: ' . $detail);
    }

    /**
     * Create an exception for a protocol version that the client does not support.
     *
     * Thrown when the request's protocol version is not present in the client
     * configuration's allowed protocol list, or when the resolved version is
     * inherently unsupported (e.g., HTTP/3 when no QUIC transport is available).
     *
     * @param ProtocolVersion $version The unsupported protocol version.
     *
     * @see Client\ClientConfiguration::$protocolVersions The configuration that governs allowed versions.
     * @see Client\Connection\ConnectorInterface Protocol version resolution rules.
     */
    public static function forUnsupportedProtocol(ProtocolVersion $version): self
    {
        return new self('Unsupported protocol version: ' . $version->value . '.');
    }

    /**
     * Create an exception for a connection that closed before the response was complete.
     *
     * Thrown when the server closes the connection (or the stream ends)
     * before the full response status line or headers have been received.
     * This typically indicates a server crash, network interruption, or
     * an intermediary (proxy or load balancer) dropping the connection.
     */
    public static function forUnexpectedEndOfStream(): self
    {
        return new self('Unexpected end of stream while reading response.');
    }

    /**
     * Create an exception for a response body that exceeds the configured maximum size.
     *
     * Thrown when the response body surpasses the limit set via
     * {@see Client\ClientConfiguration::$maxResponseBodySize}.
     * The body stream is terminated at the limit boundary; no further data
     * is read from the connection.
     *
     * @param int $limit The maximum allowed body size in bytes.
     */
    public static function forResponseBodyTooLarge(int $limit): self
    {
        return new self('Response body exceeds maximum allowed size of ' . $limit . ' bytes.');
    }
}
