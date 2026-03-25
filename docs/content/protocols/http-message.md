# HTTP Message

The `HTTP Message` component provides version-agnostic HTTP message abstractions. Requests, responses, and headers work identically across HTTP/1.0, HTTP/1.1, HTTP/2, and HTTP/3. Bodies are streaming (`ReadHandleInterface`), trailers are async (`Awaitable<FieldMap>`), and all objects are immutable.

## Requests

Construct a request with a method, URL, and optional headers/body. The request target is derived from the URL automatically.

@example('protocols/http-message-request.php')

## Responses

Responses carry a status code, headers, and an optional streaming body. Reason phrases are not stored on the response since HTTP/2 and HTTP/3 do not transmit them.

@example('protocols/http-message-response.php')

## Header Fields

`FieldMap` is an ordered, case-insensitive collection of header field pairs. It supports multiple values for the same name (e.g. Set-Cookie) and preserves insertion order and original casing.

@example('protocols/http-message-headers.php')

## Immutable Mutation

All `with*()` methods return new instances. The original message is never modified.

@example('protocols/http-message-mutation.php')

## Streaming Bodies

Bodies are modeled as `ReadHandleInterface` streams, allowing constant-memory processing of arbitrarily large payloads. Use `MemoryHandle` to create a body from a string.

@example('protocols/http-message-bodies.php')

## Trailers

Trailing header fields arrive after the body has been fully transmitted. They are modeled as `Awaitable<FieldMap>` because the values are not known until the body is complete. Use `Deferred` to create trailers that are resolved later.

@example('protocols/http-message-trailers.php')

## Transactions

`Transaction` groups the final response with any informational (1xx) responses received before it and any HTTP/2 server-pushed exchanges. This is the return type of connection-level exchange methods.

@example('protocols/http-message-transaction.php')

## Status Codes and Methods

Method and status code constants improve readability and prevent typos. The `reason_phrase()` function maps any status code to its canonical reason phrase for HTTP/1.x serialization.

@example('protocols/http-message-constants.php')

## Protocol Versions

| Version | Enum Case | Value |
|---------|-----------|-------|
| HTTP/1.0 | `ProtocolVersion::V10` | `HTTP/1.0` |
| HTTP/1.1 | `ProtocolVersion::V11` | `HTTP/1.1` |
| HTTP/2 | `ProtocolVersion::V20` | `HTTP/2` |
| HTTP/3 | `ProtocolVersion::V30` | `HTTP/3` |

## Status Code Classes

| Range | Class | Example |
|-------|-------|---------|
| 1xx | Informational | 100 Continue, 101 Switching Protocols |
| 2xx | Successful | 200 OK, 201 Created, 204 No Content |
| 3xx | Redirection | 301 Moved Permanently, 304 Not Modified |
| 4xx | Client Error | 400 Bad Request, 404 Not Found, 429 Too Many Requests |
| 5xx | Server Error | 500 Internal Server Error, 502 Bad Gateway |

## RFC Compliance

| RFC | Coverage |
|-----|----------|
| RFC 9110 | HTTP semantics: methods, status codes, header fields, message body |
| RFC 9112 | HTTP/1.1 message syntax (request line, status line, headers) |
| RFC 9113 | HTTP/2 pseudo-headers, trailers, binary framing semantics |
| RFC 9114 | HTTP/3 message mapping over QUIC |

See `src/Psl/HTTP/Message/` for the full API.
