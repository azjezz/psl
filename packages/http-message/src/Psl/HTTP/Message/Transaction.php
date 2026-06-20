<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

use Psl\Async;

/**
 * The complete result of an HTTP request/response exchange.
 *
 * Encapsulates everything produced by a single HTTP exchange: the final
 * (non-1xx) response, any informational (1xx) responses received before
 * it, and any HTTP/2 server-pushed exchanges. This is the return type of
 * connection-level exchange methods and provides consumers with a single
 * object containing all exchange artifacts.
 *
 * ## Informational responses
 *
 * HTTP servers may send one or more informational (1xx) responses before
 * the final response. These are captured in {@see $informational} in the
 * order they were received. Common informational responses include:
 *
 * - **100 Continue**: Indicates the server has received the request headers
 *   and the client should proceed to send the body (RFC 9110 Section 15.2.1).
 * - **102 Processing** (WebDAV): Indicates the request is being processed
 *   but no final response is available yet (RFC 2518).
 * - **103 Early Hints**: Allows the server to send preliminary headers
 *   (e.g., Link headers for resource preloading) before the final response
 *   (RFC 8297).
 *
 * ## Server push (HTTP/2)
 *
 * In HTTP/2, the server may proactively push resources it anticipates the
 * client will need via PUSH_PROMISE frames. Each pushed resource is
 * represented as an {@see Exchange} containing the synthetic request
 * (reconstructed from the PUSH_PROMISE header block) and its corresponding
 * response. These are available via the {@see $pushed} awaitable.
 *
 * HTTP/3 (RFC 9114) deprecated server push in practice, so pushed exchanges
 * are primarily relevant for HTTP/2 connections. For HTTP/1.x connections,
 * {@see $pushed} is always null.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.2 Informational 1xx
 * @link https://datatracker.ietf.org/doc/html/rfc8297 103 Early Hints
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.4 HTTP/2 Server Push
 * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.6 HTTP/3 Server Push
 *
 * @api
 */
final readonly class Transaction
{
    /**
     * Informational (1xx) responses received before the final response.
     *
     * These are interim responses sent by the server to indicate progress
     * before the final response is available. Common examples include:
     *
     * - 100 Continue: the server has received the request headers and the
     *   client should proceed to send the body.
     * - 102 Processing (WebDAV): the server is processing the request but
     *   no final response is available yet.
     * - 103 Early Hints: the server is sending preliminary headers (e.g.,
     *   Link headers for preloading) before the final response.
     *
     * For most requests, this list will be empty. The responses are ordered
     * chronologically as received from the server.
     *
     * @var list<Response>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.2 Informational 1xx
     * @link https://datatracker.ietf.org/doc/html/rfc8297 103 Early Hints
     */
    public array $informational;

    /**
     * HTTP/2 server-pushed exchanges, resolved when all push promises complete.
     *
     * Each pushed exchange contains the server-initiated request (reconstructed
     * from the PUSH_PROMISE frame's header block) and its corresponding response.
     * The awaitable resolves once all promised streams have completed.
     *
     * This is {@see null} for HTTP/1.x connections or when the server does not
     * send any PUSH_PROMISE frames. Callers should check for {@see null} before
     * awaiting.
     *
     * Note: HTTP/3 (RFC 9114) deprecated server push in practice, so this field
     * is primarily relevant for HTTP/2 connections.
     *
     * @var null|Async\Awaitable<list<Exchange>>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.4 HTTP/2 Server Push
     * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.6 HTTP/3 Server Push
     */
    public null|Async\Awaitable<array> $pushed;

    /**
     * The final (non-1xx) HTTP response.
     *
     * This is the terminal response from the server with a status code in the
     * range 2xx-5xx. Any preceding 1xx informational responses are captured
     * separately in {@see $informational}.
     *
     * The response body may be streamed; reading it requires consuming the
     * {@see Response::$body} read handle. Trailer headers, if present, are
     * available by awaiting {@see Response::$trailers} after the body has been
     * fully consumed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15 Status Codes
     */
    public Response $response;

    /**
     * Create a new transaction from the components of an HTTP exchange.
     *
     * @param list<Response> $informational 1xx informational responses received
     *  before the final response, in the order they were received.
     * @param null|Async\Awaitable<list<Exchange>> $pushed HTTP/2 server-pushed
     *  request/response pairs, or {@see null} if no push promises were received.
     * @param Response $response The final (2xx-5xx) response.
     */
    public function __construct(array $informational, null|Async\Awaitable<array> $pushed, Response $response)
    {
        $this->informational = $informational;
        $this->pushed = $pushed;
        $this->response = $response;
    }
}
