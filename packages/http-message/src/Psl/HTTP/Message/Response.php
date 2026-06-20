<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

use Psl\Async;
use Psl\IO;

/**
 * An HTTP response message sent from server to client.
 *
 * Represents a complete HTTP response comprising a status code, protocol
 * version, header fields, an optional streaming body, and optional trailing
 * header fields. This class extends {@see Message} with the response-specific
 * status code property defined in RFC 9110 Section 15.
 *
 * The class is readonly and immutable. All mutation is performed through the
 * with* methods, each of which returns a new instance with the specified
 * property changed while preserving all other properties. This design is
 * safe for use in middleware pipelines where multiple layers may inspect or
 * modify a response without affecting the original.
 *
 * ## Reason phrase handling
 *
 * Unlike some HTTP message abstractions, this class does not store a reason
 * phrase. HTTP/2 (RFC 9113) and HTTP/3 (RFC 9114) do not transmit reason
 * phrases on the wire, making them a purely HTTP/1.x concept. For HTTP/1.x
 * serialization, use the {@see reason_phrase()} function to obtain the
 * canonical reason phrase for a given status code.
 *
 * ## Status code classes
 *
 * The first digit of the status code defines the response class:
 *
 * - **1xx (Informational)**: The request was received, continuing process.
 *   These interim responses are captured in {@see Transaction::$informational}.
 * - **2xx (Successful)**: The request was successfully received, understood,
 *   and accepted.
 * - **3xx (Redirection)**: Further action needs to be taken to complete the
 *   request.
 * - **4xx (Client Error)**: The request contains bad syntax or cannot be
 *   fulfilled.
 * - **5xx (Server Error)**: The server failed to fulfill an apparently valid
 *   request.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15 Status Codes
 * @link https://datatracker.ietf.org/doc/html/rfc9112#section-4 HTTP/1.1 Status Line
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.3.2 HTTP/2 Response Pseudo-Header Fields
 * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.3.2 HTTP/3 Response Pseudo-Header Fields
 *
 * @api
 */
final readonly class Response extends Message
{
    /**
     * The HTTP status code.
     *
     * A three-digit integer where the first digit defines the response class:
     * 1xx informational, 2xx success, 3xx redirection, 4xx client error, and
     * 5xx server error. Standard status codes and their semantics are defined
     * in RFC 9110 Section 15.
     *
     * For HTTP/1.x, the status code appears in the status line alongside the
     * protocol version and reason phrase (e.g., "HTTP/1.1 200 OK"). For HTTP/2
     * and HTTP/3, the status code is transmitted as the `:status` pseudo-header
     * field, and no reason phrase is sent.
     *
     * Use the status code constants defined in {@see constants.php} (e.g.,
     * {@see STATUS_OK}, {@see STATUS_NOT_FOUND}) for readability.
     *
     * @var int<100, 999>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15 Status Codes
     */
    public int $status;

    /**
     * Construct a new HTTP response message.
     *
     * Creates an immutable response with the given status code and optional
     * components. The status code must be a three-digit integer in the range
     * 100-999 per RFC 9110.
     *
     * @param int<100, 999> $status The HTTP status code (e.g., 200, 404, 500). Use the status code constants from {@see constants.php} for readability.
     * @param ProtocolVersion $protocolVersion The HTTP protocol version. Defaults to HTTP/1.1.
     * @param FieldMap $headers The response header fields. Defaults to an empty field map.
     * @param null|IO\ReadHandleInterface $body The response body as a streaming read handle, or null for bodyless responses (1xx, 204, 304, or responses to HEAD requests).
     * @param null|Async\Awaitable<FieldMap> $trailers An awaitable resolving to trailing header fields, or null when no trailers are expected.
     */
    public function __construct(
        int $status,
        ProtocolVersion $protocolVersion = ProtocolVersion::V11,
        FieldMap $headers = new FieldMap(),
        null|IO\ReadHandleInterface $body = null,
        null|Async\Awaitable<FieldMap> $trailers = null,
    ) {
        $this->status = $status;

        parent::__construct($protocolVersion, $headers, $body, $trailers);
    }

    /**
     * Return a new response with the given status code.
     *
     * Creates a new {@see Response} instance identical to this one except for
     * the status code. All other properties, including headers, body, and
     * trailers, are preserved.
     *
     * @param int<100, 999> $status The HTTP status code for the new response (e.g., 200, 404, 500).
     *
     * @return self A new response instance with the specified status code.
     */
    public function withStatus(int $status): self
    {
        return new self($status, $this->protocolVersion, $this->headers, $this->body, $this->trailers);
    }

    /**
     * Return a new response with the given protocol version.
     *
     * Creates a new {@see Response} instance with the specified protocol
     * version. This is typically used when constructing responses for a
     * specific protocol version during serialization.
     *
     * @param ProtocolVersion $protocolVersion The desired HTTP protocol version for the new response.
     *
     * @return self A new response instance with the specified protocol version.
     */
    public function withProtocolVersion(ProtocolVersion $protocolVersion): self
    {
        return new self($this->status, $protocolVersion, $this->headers, $this->body, $this->trailers);
    }

    /**
     * Return a new response with the given header field map, replacing all headers.
     *
     * Creates a new {@see Response} instance with the provided {@see FieldMap},
     * completely replacing all existing header fields. Use this when you need
     * to set the entire header section at once, for example when reconstructing
     * a response from parsed data.
     *
     * @param FieldMap $headers The complete set of header fields for the new response.
     *
     * @return self A new response instance with the specified headers.
     */
    public function withHeaders(FieldMap $headers): self
    {
        return new self($this->status, $this->protocolVersion, $headers, $this->body, $this->trailers);
    }

    /**
     * Return a new response with the named header replaced or added.
     *
     * If a header with the same name already exists (case-insensitive match),
     * all of its values are removed and replaced with the single new value.
     * If the header does not exist, it is appended. This delegates to
     * {@see FieldMap::with()}.
     *
     * @param non-empty-string $name The header field name (e.g., "Content-Type", "Cache-Control"). Matched case-insensitively per RFC 9110 Section 5.1.
     * @param string $value The header field value.
     *
     * @return self A new response instance with the specified header set.
     */
    public function withHeader(string $name, string $value): self
    {
        return $this->withHeaders($this->headers->with($name, $value));
    }

    /**
     * Return a new response with an additional header value appended.
     *
     * Unlike {@see withHeader()}, this preserves all existing values for the
     * given header name and appends the new value. This is appropriate for
     * headers that support multiple values, such as Set-Cookie, Vary, or
     * Via. This delegates to {@see FieldMap::withAdded()}.
     *
     * @param non-empty-string $name The header field name. Matched case-insensitively per RFC 9110 Section 5.1.
     * @param string $value The additional header field value to append.
     *
     * @return self A new response instance with the additional header value appended.
     */
    public function withAddedHeader(string $name, string $value): self
    {
        return $this->withHeaders($this->headers->withAdded($name, $value));
    }

    /**
     * Return a new response with the named header removed.
     *
     * Removes all values associated with the given header name
     * (case-insensitive match). If the header does not exist, the returned
     * response is functionally identical to this one. This delegates to
     * {@see FieldMap::without()}.
     *
     * @param non-empty-string $name The header field name to remove. Matched case-insensitively per RFC 9110 Section 5.1.
     *
     * @return self A new response instance with the specified header removed.
     */
    public function withoutHeader(string $name): self
    {
        return $this->withHeaders($this->headers->without($name));
    }

    /**
     * Return a new response with the given body stream.
     *
     * Creates a new {@see Response} instance with the specified body. Note
     * that responses to HEAD requests and responses with 1xx, 204, or 304
     * status codes must not include a body per RFC 9110 Section 6.4.1.
     *
     * @param null|IO\ReadHandleInterface $body The response body as a streaming read handle, or null to create a bodyless response.
     *
     * @return self A new response instance with the specified body.
     */
    public function withBody(null|IO\ReadHandleInterface $body): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers, $body, $this->trailers);
    }

    /**
     * Return a new response with the given trailing headers.
     *
     * Creates a new {@see Response} instance with the specified trailers.
     * Trailers are only meaningful for responses that use chunked transfer
     * encoding in HTTP/1.1 or DATA frames in HTTP/2 and HTTP/3. The sender
     * should declare expected trailer fields using the Trailer header per
     * RFC 9110 Section 6.5.1.
     *
     * @param null|Async\Awaitable<FieldMap> $trailers An awaitable resolving to trailing header fields, or null when no trailers are expected.
     *
     * @return self A new response instance with the specified trailers.
     */
    public function withTrailers(null|Async\Awaitable<FieldMap> $trailers): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers, $this->body, $trailers);
    }
}
