# HTTP Client

The `HTTP Client` component provides an async HTTP client with connection pooling, HTTP/2 multiplexing, automatic protocol negotiation via ALPN, middleware support, and composable decorators for redirects and retries. All I/O is non-blocking and built on the PSL async runtime.

## Basic Usage

Create a client, build a request, and send it. The returned transaction contains the response with status, headers, and a streaming body.

@example('protocols/http-client-basic.php')

## Sending Data

Send a POST request with a JSON body and custom headers using `IO\MemoryHandle` for the request body.

@example('protocols/http-client-sending-data.php')

## Concurrent Requests

Use `Async\concurrently()` to send multiple requests in parallel over the same client. HTTP/2 connections are automatically multiplexed.

@example('protocols/http-client-concurrent.php')

## Redirects

`RedirectClient` wraps any client and automatically follows 3xx redirects with credential stripping on cross-origin hops and method rewriting per RFC 9110.

@example('protocols/http-client-redirects.php')

## Retries

`RetryClient` wraps any client and retries idempotent requests on transport-level failures with exponential backoff.

@example('protocols/http-client-retries.php')

## Configuration

`ClientConfiguration` controls TLS settings, protocol version preferences, response size limits, base URL, and proxy configuration.

@example('protocols/http-client-configuration.php')

## Per-Request Overrides

`SendConfiguration` overrides specific client settings for a single request without affecting the client default.

@example('protocols/http-client-send-configuration.php')

## SSRF Protection

`DeniedDestinationsMiddleware` inspects the resolved peer IP after connection establishment and blocks requests to private, loopback, and link-local addresses.

@example('protocols/http-client-ssrf.php')

## SOCKS Proxy

Route all TCP connections through a SOCKS5 proxy. TLS and ALPN negotiation happen on top of the tunneled connection.

@example('protocols/http-client-proxy.php')

## HTTP Tunnel

Use an HTTP CONNECT tunnel for HTTPS connections through a forward proxy.

@example('protocols/http-client-tunnel.php')

## HTTP/2 over Plaintext (h2c)

For servers that support HTTP/2 without TLS, set `protocolVersions` to only `ProtocolVersion::V20`. The client uses prior-knowledge mode per RFC 9113 Section 3.4 - it sends the HTTP/2 connection preface directly on the TCP connection, skipping TLS and ALPN negotiation entirely.

@example('protocols/http-client-h2c.php')

## Error Handling

The client throws a structured exception hierarchy. Transport-level exceptions from `Psl\Network` and `Psl\IO` propagate unwrapped.

@example('protocols/http-client-errors.php')

## Exception Hierarchy

| Exception | When Thrown |
|-----------|------------|
| `Exception\RuntimeException` | Base class for all HTTP client errors |
| `Exception\RequestException` | Request is invalid (missing URL, malformed target) |
| `Exception\ProtocolException` | Malformed response, unsupported protocol, body size exceeded |
| `Exception\TooManyRedirectsException` | Redirect limit exceeded (via `RedirectClient`) |
| `Network\Exception\RuntimeException` | Connection refused, DNS failure, connect timeout |
| `IO\Exception\RuntimeException` | Read/write failure on the underlying socket |
| `Async\Exception\CancelledException` | Cancellation token fired during any stage |

## Protocol Support

| Version | Transport | Negotiation | Multiplexing |
|---------|-----------|-------------|--------------|
| HTTP/1.0 | TCP / TLS | Explicit | No |
| HTTP/1.1 | TCP / TLS | Default fallback | No (persistent connections) |
| HTTP/2 | TLS (h2) / TCP (h2c) | ALPN | Yes (streams over single connection) |
| HTTP/3 | QUIC | ALPN | Yes (independent streams, no HOL blocking) |

## RFC Compliance

| RFC | Coverage |
|-----|----------|
| RFC 1945 | HTTP/1.0 message format |
| RFC 9110 | HTTP semantics, methods, status codes, redirects, idempotency |
| RFC 9112 | HTTP/1.1 message syntax, chunked transfer encoding, persistent connections |
| RFC 9113 | HTTP/2 binary framing, HPACK, flow control, server push, multiplexing |
| RFC 9114 | HTTP/3 over QUIC |
| RFC 3986 | URI resolution for base URL and redirect Location headers |
| RFC 1928 | SOCKS5 proxy protocol |
| RFC 7231 | Historical redirect method rewriting (301/302 to GET) |

See `src/Psl/HTTP/Client/` for the full API.
