<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

/**
 * HTTP protocol version identifier.
 *
 * Enumerates the HTTP protocol versions supported by the library. Each case
 * holds the canonical version string as its backed value, as defined by the
 * respective RFC. For HTTP/1.x, this is the version token transmitted in
 * request lines and status lines (e.g., "HTTP/1.1"). For HTTP/2 and HTTP/3,
 * the protocol version is not sent on the wire but is instead identified
 * during connection establishment via ALPN negotiation; the string values
 * match the official protocol names.
 *
 * The protocol version affects multiple aspects of message handling:
 *
 * - **Serialization**: HTTP/1.x messages use text-based request/status lines,
 *   while HTTP/2 and HTTP/3 use binary framing.
 * - **Connection behavior**: HTTP/1.0 uses one request per connection by
 *   default, HTTP/1.1 supports persistent connections, and HTTP/2 and HTTP/3
 *   multiplex multiple streams over a single connection.
 * - **Feature availability**: Trailers, server push, and header compression
 *   have different support levels across versions.
 * - **Connector behavior**: The protocol version influences how HTTP clients
 *   establish connections and negotiate protocols via ALPN.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-2.5 Protocol Version
 * @link https://datatracker.ietf.org/doc/html/rfc1945 HTTP/1.0
 * @link https://datatracker.ietf.org/doc/html/rfc9112 HTTP/1.1
 * @link https://datatracker.ietf.org/doc/html/rfc9113 HTTP/2
 * @link https://datatracker.ietf.org/doc/html/rfc9114 HTTP/3
 *
 * @api
 */
enum ProtocolVersion: string
{
    /**
     * HTTP/1.0 as defined by RFC 1945.
     *
     * The original HTTP protocol version with a simple request/response model.
     * Each request requires a separate TCP connection; persistent connections
     * are not supported by default (though some implementations honor the
     * non-standard "Connection: keep-alive" header). This version does not
     * support chunked transfer encoding, Host header requirements, or trailers.
     *
     * HTTP/1.0 is rarely used in modern applications but may be encountered
     * with legacy servers or proxies.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc1945 HTTP/1.0 Specification
     */
    case V10 = 'HTTP/1.0';

    /**
     * HTTP/1.1 as defined by RFC 9112.
     *
     * The most widely deployed HTTP version, introducing persistent connections
     * (keep-alive) by default, chunked transfer encoding for streaming bodies
     * of unknown length, the mandatory Host header for virtual hosting, and
     * trailer fields in chunked transfers. This is the default protocol version
     * for {@see Request} and {@see Response} instances.
     *
     * HTTP/1.1 messages are text-based, with the method, request target, and
     * protocol version serialized as the request line, and the status code,
     * reason phrase, and protocol version as the status line.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9112 HTTP/1.1 Message Syntax and Routing
     * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
     */
    case V11 = 'HTTP/1.1';

    /**
     * HTTP/2 as defined by RFC 9113.
     *
     * A major revision that introduces binary framing, multiplexed streams
     * over a single TCP connection, HPACK header compression, flow control,
     * stream prioritization, and server push via PUSH_PROMISE frames. HTTP/2
     * eliminates head-of-line blocking at the HTTP level (though TCP-level
     * head-of-line blocking remains).
     *
     * HTTP/2 is negotiated via ALPN ("h2") during the TLS handshake for
     * encrypted connections, or via prior knowledge ("h2c") for plaintext
     * connections. Pseudo-header fields (`:method`, `:path`, `:scheme`,
     * `:authority`, `:status`) replace the HTTP/1.x request/status lines.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113 HTTP/2 Specification
     * @link https://datatracker.ietf.org/doc/html/rfc7301 TLS ALPN Extension
     */
    case V20 = 'HTTP/2';

    /**
     * HTTP/3 as defined by RFC 9114.
     *
     * Built on QUIC (RFC 9000) instead of TCP, HTTP/3 provides independent
     * streams without head-of-line blocking at both the HTTP and transport
     * levels. It uses QPACK header compression (RFC 9204) instead of HPACK,
     * and integrates TLS 1.3 directly into the transport handshake, reducing
     * connection establishment latency.
     *
     * HTTP/3 is negotiated via ALPN ("h3") and uses the same pseudo-header
     * field mapping as HTTP/2. Server push is technically supported but
     * deprecated in practice.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9114 HTTP/3 Specification
     * @link https://datatracker.ietf.org/doc/html/rfc9000 QUIC Transport Protocol
     * @link https://datatracker.ietf.org/doc/html/rfc9204 QPACK Header Compression
     */
    case V30 = 'HTTP/3';
}
