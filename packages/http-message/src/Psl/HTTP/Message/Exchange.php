<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

/**
 * Represents a paired HTTP request and response from a single exchange.
 *
 * An exchange captures both sides of a complete HTTP interaction: the request
 * that was sent (or, in the case of server push, the request that the server
 * initiated on the client's behalf) and the corresponding response. This
 * provides a self-contained record of what was requested and what was received.
 *
 * ## Primary use case: HTTP/2 server push
 *
 * This type is primarily used to represent HTTP/2 server-pushed exchanges
 * within a {@see Transaction}. When a server anticipates resources the client
 * will need, it proactively sends PUSH_PROMISE frames followed by the
 * promised responses. Each pushed exchange contains:
 *
 * - A synthetic {@see Request} reconstructed from the PUSH_PROMISE frame's
 *   header block. This allows consumers to identify which resource was pushed
 *   and match it against future client-initiated requests. The method is
 *   always safe and cacheable (typically GET), and the request never includes
 *   a body per RFC 9113 Section 8.4.1.
 * - The {@see Response} delivered on the promised stream. The response body
 *   may be streamed and should be consumed via the {@see Response::$body}
 *   read handle.
 *
 * ## General exchange representation
 *
 * While designed primarily for server push, this type can also represent
 * any client-initiated exchange where both the request and response need
 * to be captured together, such as in logging, caching, or replay scenarios.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.4 HTTP/2 Server Push
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.4.1 PUSH_PROMISE Constraints
 * @link https://datatracker.ietf.org/doc/html/rfc9114#section-4.6 HTTP/3 Server Push
 *
 * @api
 */
final readonly class Exchange
{
    /**
     * The HTTP request associated with this exchange.
     *
     * For server-pushed exchanges, this is a synthetic request reconstructed
     * from the PUSH_PROMISE frame's header block. It represents the request
     * the server assumed the client would make. The method will always be a
     * safe, cacheable method (typically GET), and it will never include a body.
     *
     * For client-initiated exchanges, this is the original request as sent.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-8.4.1 PUSH_PROMISE Constraints
     */
    public Request $request;

    /**
     * The HTTP response received for this exchange.
     *
     * For server-pushed exchanges, this is the response delivered on the
     * promised stream. The response body may be streamed; reading it requires
     * consuming the {@see Response::$body} read handle. Trailer headers, if
     * present, are available by awaiting {@see Response::$trailers} after the
     * body has been fully consumed.
     */
    public Response $response;

    /**
     * Create a new exchange from a request/response pair.
     *
     * @param Request $request The request that initiated or represents this exchange.
     * @param Response $response The response received for this exchange.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
