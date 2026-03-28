<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

use Psl\Async;
use Psl\IO;

/**
 * Base HTTP message shared by requests and responses.
 *
 * Contains the four components common to all HTTP messages across all protocol
 * versions: the protocol version identifier, header fields, an optional
 * streaming body, and optional trailing header fields. Both {@see Request}
 * and {@see Response} extend this class, adding their respective
 * direction-specific properties (method and URL for requests, status code
 * for responses).
 *
 * The body is modeled as a streaming {@see IO\ReadHandleInterface} rather than
 * a buffered string. This design allows constant-memory processing of
 * arbitrarily large payloads, which is essential for file uploads, chunked
 * downloads, and server-sent event streams. The body handle is consumed once;
 * callers must buffer the content themselves if multiple reads are needed.
 *
 * Trailers are modeled as an {@see Async\Awaitable} because trailing header
 * fields are not available until after the body has been fully transmitted.
 * In HTTP/1.1 chunked transfers (RFC 9112 Section 7.1.2), trailers follow
 * the final chunk. In HTTP/2 (RFC 9113 Section 8.1), trailers are sent as a
 * HEADERS frame after all DATA frames on the stream. In HTTP/3 (RFC 9114),
 * trailers follow the same pattern over QUIC streams. The awaitable resolves
 * once the trailing {@see FieldMap} is available.
 *
 * This class is readonly and immutable. All mutation is performed through
 * the with* methods on the concrete subclasses, which return new instances.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-6 HTTP Messages
 * @link https://datatracker.ietf.org/doc/html/rfc9112#section-7.1.2 Chunked Transfer Coding Trailers
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.1 HTTP/2 HTTP Message Exchanges
 * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.1 HTTP/3 HTTP Message Exchanges
 *
 * @inheritors Request|Response
 *
 * @api
 */
abstract readonly class Message
{
    /**
     * The HTTP protocol version for this message.
     *
     * Identifies which version of the HTTP protocol this message conforms to.
     * For HTTP/1.x messages, this corresponds to the version token in the
     * request line or status line (e.g., "HTTP/1.1"). For HTTP/2 and HTTP/3,
     * the protocol version is not transmitted on the wire but is determined
     * during connection establishment via ALPN negotiation.
     *
     * The protocol version influences message serialization behavior. For
     * example, HTTP/1.0 does not support chunked transfer encoding, while
     * HTTP/2 uses binary framing instead of text-based request/status lines.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-2.5 Protocol Version
     */
    public ProtocolVersion $protocolVersion;

    /**
     * The header fields for this message.
     *
     * Contains all header fields (known as "header section" in RFC 9110) as
     * an ordered, case-insensitive collection. Field names are compared
     * case-insensitively per RFC 9110 Section 5.1, and multiple values for
     * the same field name are preserved in insertion order.
     *
     * For HTTP/1.x, these correspond to the header lines between the
     * start line and the empty line preceding the body. For HTTP/2 and
     * HTTP/3, these are the non-pseudo-header fields from the HEADERS frame.
     * Pseudo-headers (`:method`, `:path`, `:scheme`, `:authority`, `:status`)
     * are not included here; they are represented by dedicated properties on
     * {@see Request} and {@see Response}.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-5 Fields
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-6.3 Header Fields
     */
    public FieldMap $headers;

    /**
     * The message body as a readable stream handle, or null if no body is present.
     *
     * When present, the body is a streaming {@see IO\ReadHandleInterface} that
     * provides the message payload. The handle is consumed once; reading from
     * it advances an internal cursor, so callers should not attempt to read
     * the same handle multiple times without buffering the content.
     *
     * For requests, the body carries the payload for methods like POST and PUT.
     * GET, HEAD, DELETE, and other methods typically have no body. For responses,
     * the body carries the response payload. Responses to HEAD requests and
     * responses with 1xx, 204, or 304 status codes must not include a body
     * per RFC 9110 Section 6.4.1.
     *
     * In HTTP/1.1, the body length is determined by the Content-Length header
     * or chunked transfer encoding. In HTTP/2 and HTTP/3, the body is carried
     * as DATA frames on the stream.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-6.4 Content
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-6.4.1 Content Semantics
     */
    public null|IO\ReadHandleInterface $body;

    /**
     * Trailing header fields that arrive after the body.
     *
     * Trailers allow the sender to include additional metadata after the
     * message body has been fully transmitted. This is useful for fields
     * whose values are not known until the body has been generated, such as
     * message integrity checksums or digital signatures.
     *
     * In HTTP/1.1 chunked transfers (RFC 9112 Section 7.1.2), trailers are
     * sent after the final zero-length chunk. In HTTP/2 (RFC 9113 Section 8.1),
     * trailers are delivered as a HEADERS frame with the END_STREAM flag set,
     * following all DATA frames on the stream. In HTTP/3 (RFC 9114 Section 4.1),
     * trailers follow the same pattern over QUIC streams.
     *
     * The awaitable resolves to the trailing {@see FieldMap} once the trailers
     * have been received. This is {@see null} when no trailers are expected,
     * either because the sender did not include a Trailer header field or
     * because the transfer encoding does not support trailers.
     *
     * @var null|Async\Awaitable<FieldMap>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-6.5 Trailer Fields
     * @link https://datatracker.ietf.org/doc/html/rfc9112#section-7.1.2 Chunked Trailer Section
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.1 HTTP/2 Trailers
     */
    public null|Async\Awaitable $trailers;

    /**
     * Construct a new HTTP message with the given components.
     *
     * This constructor is invoked by the concrete subclasses {@see Request}
     * and {@see Response}. It is not intended to be called directly.
     *
     * @param ProtocolVersion $protocolVersion The HTTP protocol version this message conforms to. Influences serialization behavior for HTTP/1.x and is informational for HTTP/2 and HTTP/3.
     * @param FieldMap $headers The header fields for this message. Field names are treated case-insensitively per RFC 9110 Section 5.1.
     * @param null|IO\ReadHandleInterface $body The message body as a streaming read handle, or null if the message carries no payload.
     * @param null|Async\Awaitable<FieldMap> $trailers An awaitable that resolves to trailing header fields once they are available, or null when no trailers are expected.
     */
    public function __construct(
        ProtocolVersion $protocolVersion,
        FieldMap $headers,
        null|IO\ReadHandleInterface $body,
        null|Async\Awaitable $trailers,
    ) {
        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;
        $this->trailers = $trailers;
    }
}
