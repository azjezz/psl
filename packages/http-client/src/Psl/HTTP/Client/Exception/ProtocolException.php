<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\HTTP\Message\ProtocolVersion;

/**
 * Thrown when the server sends a malformed or unexpected response, when a
 * requested protocol version is not supported, or when a response body
 * exceeds the configured size limit.
 *
 * This covers violations of HTTP message syntax (RFC 9112 for HTTP/1.x,
 * RFC 9113 for HTTP/2), unsupported protocol negotiation, premature stream
 * termination, and response body size enforcement.
 *
 * @see RuntimeException Parent exception for all HTTP client errors.
 * @see RequestException For errors originating from the request itself.
 */
final class ProtocolException extends RuntimeException
{
    /**
     * Create an exception for a response that violates HTTP message syntax.
     *
     * @param string $detail Human-readable description of the malformation.
     *
     * @return self
     */
    public static function forMalformedResponse(string $detail): self
    {
        return new self('Malformed HTTP response: ' . $detail);
    }

    /**
     * Create an exception for a protocol version that the client does not support.
     *
     * @param ProtocolVersion $version The unsupported protocol version.
     *
     * @return self
     */
    public static function forUnsupportedProtocol(ProtocolVersion $version): self
    {
        return new self('Unsupported protocol version: ' . $version->value . '.');
    }

    /**
     * Create an exception for a connection that closed before the response was complete.
     *
     * @return self
     */
    public static function forUnexpectedEndOfStream(): self
    {
        return new self('Unexpected end of stream while reading response.');
    }

    /**
     * Create an exception for a response body that exceeds the configured maximum size.
     *
     * @param int $limit The maximum allowed body size in bytes.
     *
     * @return self
     */
    public static function forResponseBodyTooLarge(int $limit): self
    {
        return new self('Response body exceeds maximum allowed size of ' . $limit . ' bytes.');
    }
}
