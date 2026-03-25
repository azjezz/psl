<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

use Psl\Async;
use Psl\IO;
use Psl\URL\URL;

/**
 * An HTTP request message sent from client to server.
 *
 * Represents a complete HTTP request comprising a method, target URL, request
 * target, protocol version, header fields, an optional streaming body, and
 * optional trailing header fields. This class extends {@see Message} with the
 * request-specific properties defined in RFC 9110 Section 3.4.
 *
 * The class is readonly and immutable. All mutation is performed through the
 * with* methods, each of which returns a new instance with the specified
 * property changed while preserving all other properties. This design is
 * safe for use in middleware pipelines where multiple layers may need to
 * modify a request without affecting the original.
 *
 * ## Request target derivation
 *
 * The request target is the string that appears in the HTTP/1.x request line
 * (e.g., "GET /path?query HTTP/1.1") or the `:path` pseudo-header in HTTP/2
 * and HTTP/3. When not explicitly provided, it is derived from the URL: the
 * path component (defaulting to "/") plus the query string if present. An
 * explicit request target can be set for special forms such as the asterisk
 * form ("*") used with OPTIONS, or the authority form used with CONNECT.
 *
 * ## Protocol-specific considerations
 *
 * - **HTTP/1.x**: The method, request target, and protocol version are
 *   serialized as the request line per RFC 9112 Section 3.
 * - **HTTP/2**: The method maps to the `:method` pseudo-header, the URL
 *   scheme to `:scheme`, the URL authority to `:authority`, and the request
 *   target to `:path` per RFC 9113 Section 8.3.1.
 * - **HTTP/3**: Uses the same pseudo-header mapping as HTTP/2 per RFC 9114
 *   Section 4.3.1.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9 Methods
 * @link https://datatracker.ietf.org/doc/html/rfc9112#section-3 HTTP/1.1 Request Line
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.3.1 HTTP/2 Request Pseudo-Header Fields
 * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.3.1 HTTP/3 Request Pseudo-Header Fields
 *
 * @api
 */
final readonly class Request extends Message
{
    /**
     * The request target, typically the path and query components of the URL.
     *
     * This is the string that identifies the target resource for the request.
     * In most cases it takes the origin form: the absolute path followed by
     * an optional query string (e.g., "/index.html?page=2"). Other forms
     * defined by RFC 9112 Section 3.2 include the absolute form (a complete
     * URI), the authority form (used with CONNECT), and the asterisk form
     * ("*", used with OPTIONS).
     *
     * For HTTP/1.x, this value appears directly in the request line between
     * the method and the HTTP version. For HTTP/2 and HTTP/3, it maps to
     * the `:path` pseudo-header field.
     *
     * When not explicitly provided in the constructor, the request target is
     * derived from {@see $url}: the path component (defaulting to "/" when
     * empty) plus the query string if present. If neither a URL nor an
     * explicit request target is provided, this defaults to "/".
     *
     * @var non-empty-string
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9112#section-3.2 Request Target
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.3.1 HTTP/2 :path Pseudo-Header
     */
    public string $requestTarget;

    /**
     * The HTTP method in uppercase (e.g., "GET", "POST", "DELETE").
     *
     * The method indicates the desired action to be performed on the target
     * resource. Standard methods are defined in RFC 9110 Section 9, and
     * extension methods (such as PATCH from RFC 5789 and WebDAV methods from
     * RFC 4918) are also supported.
     *
     * Methods are case-sensitive and conventionally uppercase. The method
     * semantics determine whether a request body is expected: GET, HEAD,
     * DELETE, and OPTIONS typically have no body, while POST, PUT, and PATCH
     * typically do.
     *
     * For HTTP/1.x, the method appears at the start of the request line.
     * For HTTP/2 and HTTP/3, it maps to the `:method` pseudo-header field.
     *
     * @var non-empty-uppercase-string
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9 Methods
     * @link https://datatracker.ietf.org/doc/html/rfc5789 PATCH Method
     * @link https://datatracker.ietf.org/doc/html/rfc4918 WebDAV Methods
     */
    public string $method;

    /**
     * The target URL, or null when only the request target is available.
     *
     * Contains the full URL identifying the target resource, including the
     * scheme, authority (host and optional port), path, and query components.
     * This is typically set for client-initiated requests where the full URL
     * is known.
     *
     * This is {@see null} for server-side requests where only the request
     * target is available from the request line or pseudo-headers. It may
     * also be null for requests using special request-target forms such as
     * the asterisk form ("*") with OPTIONS.
     *
     * For HTTP/2 and HTTP/3, the URL components are reconstructed from the
     * `:scheme`, `:authority`, and `:path` pseudo-header fields.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-7.1 Determining the Target Resource
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.3.1 HTTP/2 Pseudo-Header Fields
     */
    public null|URL $url;

    /**
     * Construct a new HTTP request message.
     *
     * Creates an immutable request with the given method, URL, and optional
     * components. When the request target is not explicitly provided, it is
     * derived from the URL path and query components. When neither URL nor
     * request target is provided, the request target defaults to "/".
     *
     * @param non-empty-uppercase-string $method The HTTP method for this request (e.g., "GET", "POST"). Must be uppercase per convention.
     * @param null|URL $url The target URL identifying the resource, or null when only a request target is available.
     * @param non-empty-string|null $requestTarget Explicit request target string, or null to derive from the URL. Use this for special forms like "*" (OPTIONS) or authority form (CONNECT).
     * @param ProtocolVersion $protocolVersion The HTTP protocol version. Defaults to HTTP/1.1, which is the most common version for client-initiated requests.
     * @param FieldMap $headers The request header fields. Defaults to an empty field map.
     * @param null|IO\ReadHandleInterface $body The request body as a streaming read handle, or null for bodyless requests (GET, HEAD, DELETE, etc.).
     * @param null|Async\Awaitable<FieldMap> $trailers An awaitable resolving to trailing header fields, or null when no trailers are expected.
     */
    public function __construct(
        string $method,
        null|URL $url,
        null|string $requestTarget = null,
        ProtocolVersion $protocolVersion = ProtocolVersion::V11,
        FieldMap $headers = new FieldMap(),
        null|IO\ReadHandleInterface $body = null,
        null|Async\Awaitable $trailers = null,
    ) {
        $this->method = $method;
        $this->url = $url;
        if ($requestTarget !== null) {
            $this->requestTarget = $requestTarget;
        } elseif ($this->url !== null) {
            $target = $this->url->path === '' ? '/' : $this->url->path;
            if ($this->url->query !== null) {
                $target .= '?' . $this->url->query;
            }

            $this->requestTarget = $target;
        } else {
            $this->requestTarget = '/';
        }

        parent::__construct($protocolVersion, $headers, $body, $trailers);
    }

    /**
     * Return a new request with the given HTTP method.
     *
     * Creates a new {@see Request} instance identical to this one except for
     * the method. All other properties, including the URL, request target,
     * headers, body, and trailers, are preserved.
     *
     * @param non-empty-uppercase-string $method The HTTP method for the new request (e.g., "GET", "POST"). Must be uppercase per convention.
     *
     * @return self A new request instance with the specified method.
     */
    public function withMethod(string $method): self
    {
        return new self(
            $method,
            $this->url,
            $this->requestTarget,
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the given target URL.
     *
     * Creates a new {@see Request} instance with the specified URL. The
     * request target is re-derived from the new URL's path and query
     * components; any previously set explicit request target is discarded.
     *
     * @param null|URL $url The new target URL, or null to clear the URL while preserving the existing request target.
     *
     * @return self A new request instance with the specified URL and a re-derived request target.
     */
    public function withUrl(null|URL $url): self
    {
        return new self(
            $this->method,
            $url,
            null,
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the given explicit request target.
     *
     * Creates a new {@see Request} instance with an explicitly set request
     * target, bypassing the automatic derivation from the URL. This is
     * useful for special request-target forms such as the asterisk form
     * ("*") used with OPTIONS requests, or the authority form
     * ("host:port") used with CONNECT.
     *
     * @param non-empty-string $requestTarget The explicit request target string (e.g., "/path?query", "*", or "host:port").
     *
     * @return self A new request instance with the specified request target.
     */
    public function withRequestTarget(string $requestTarget): self
    {
        return new self(
            $this->method,
            $this->url,
            $requestTarget,
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the given protocol version.
     *
     * Creates a new {@see Request} instance with the specified protocol
     * version. Changing the protocol version may affect how the request is
     * serialized and transmitted. For example, setting {@see ProtocolVersion::V20}
     * signals that the request should be sent over an HTTP/2 connection.
     *
     * @param ProtocolVersion $protocolVersion The desired HTTP protocol version for the new request.
     *
     * @return self A new request instance with the specified protocol version.
     */
    public function withProtocolVersion(ProtocolVersion $protocolVersion): self
    {
        return new self(
            $this->method,
            $this->url,
            $this->requestTarget,
            $protocolVersion,
            $this->headers,
            $this->body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the given header field map, replacing all headers.
     *
     * Creates a new {@see Request} instance with the provided {@see FieldMap},
     * completely replacing all existing header fields. Use this when you need
     * to set the entire header section at once, for example when reconstructing
     * a request from parsed data.
     *
     * @param FieldMap $headers The complete set of header fields for the new request.
     *
     * @return self A new request instance with the specified headers.
     */
    public function withHeaders(FieldMap $headers): self
    {
        return new self(
            $this->method,
            $this->url,
            $this->requestTarget,
            $this->protocolVersion,
            $headers,
            $this->body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the named header replaced or added.
     *
     * If a header with the same name already exists (case-insensitive match),
     * all of its values are removed and replaced with the single new value.
     * If the header does not exist, it is appended. This delegates to
     * {@see FieldMap::with()}.
     *
     * @param non-empty-string $name The header field name (e.g., "Content-Type", "Authorization"). Matched case-insensitively per RFC 9110 Section 5.1.
     * @param string $value The header field value.
     *
     * @return self A new request instance with the specified header set.
     */
    public function withHeader(string $name, string $value): self
    {
        return $this->withHeaders($this->headers->with($name, $value));
    }

    /**
     * Return a new request with an additional header value appended.
     *
     * Unlike {@see withHeader()}, this preserves all existing values for the
     * given header name and appends the new value. This is appropriate for
     * headers that support multiple values, such as Cookie, Accept, or
     * Via. This delegates to {@see FieldMap::withAdded()}.
     *
     * @param non-empty-string $name The header field name. Matched case-insensitively per RFC 9110 Section 5.1.
     * @param string $value The additional header field value to append.
     *
     * @return self A new request instance with the additional header value appended.
     */
    public function withAddedHeader(string $name, string $value): self
    {
        return $this->withHeaders($this->headers->withAdded($name, $value));
    }

    /**
     * Return a new request with the named header removed.
     *
     * Removes all values associated with the given header name
     * (case-insensitive match). If the header does not exist, the returned
     * request is functionally identical to this one. This delegates to
     * {@see FieldMap::without()}.
     *
     * @param non-empty-string $name The header field name to remove. Matched case-insensitively per RFC 9110 Section 5.1.
     *
     * @return self A new request instance with the specified header removed.
     */
    public function withoutHeader(string $name): self
    {
        return $this->withHeaders($this->headers->without($name));
    }

    /**
     * Return a new request with the given body stream.
     *
     * Creates a new {@see Request} instance with the specified body. The
     * caller is responsible for setting the appropriate Content-Type and
     * Content-Length headers (or using chunked transfer encoding) when a
     * body is present.
     *
     * @param null|IO\ReadHandleInterface $body The request body as a streaming read handle, or null to create a bodyless request.
     *
     * @return self A new request instance with the specified body.
     */
    public function withBody(null|IO\ReadHandleInterface $body): self
    {
        return new self(
            $this->method,
            $this->url,
            $this->requestTarget,
            $this->protocolVersion,
            $this->headers,
            $body,
            $this->trailers,
        );
    }

    /**
     * Return a new request with the given trailing headers.
     *
     * Creates a new {@see Request} instance with the specified trailers.
     * Trailers are only meaningful for requests that use chunked transfer
     * encoding in HTTP/1.1 or DATA frames in HTTP/2 and HTTP/3. The sender
     * should declare expected trailer fields using the Trailer header.
     *
     * @param null|Async\Awaitable<FieldMap> $trailers An awaitable resolving to trailing header fields, or null when no trailers are expected.
     *
     * @return self A new request instance with the specified trailers.
     */
    public function withTrailers(null|Async\Awaitable $trailers): self
    {
        return new self(
            $this->method,
            $this->url,
            $this->requestTarget,
            $this->protocolVersion,
            $this->headers,
            $this->body,
            $trailers,
        );
    }
}
