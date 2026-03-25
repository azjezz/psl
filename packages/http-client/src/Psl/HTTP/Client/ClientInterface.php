<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;

/**
 * Primary entry point for sending HTTP requests.
 *
 * A client sends an HTTP request and returns a {@see Transaction} containing the
 * final response, any informational (1xx) responses received before it, and any
 * HTTP/2 server-pushed exchanges. The client is responsible for URL resolution,
 * connection establishment, optional middleware execution, and the HTTP exchange
 * itself.
 *
 * Implementations may add cross-cutting behavior such as automatic redirect
 * following ({@see RedirectClient}), retry logic ({@see RetryClient}), or
 * connection-level middleware (e.g., {@see Middleware\DeniedDestinationsMiddleware}
 * for SSRF protection). These behaviors are typically composed by wrapping one
 * client implementation inside another (decorator pattern).
 *
 * ## Configuration
 *
 * Each client holds a default {@see ClientConfiguration} that governs transport
 * settings such as protocol version preferences, TLS configuration, response size
 * limits, and HTTP/2 session parameters. Callers may override these defaults on a
 * per-request basis by passing a configuration to {@see send()}.
 *
 * ## Error handling
 *
 * Exceptions fall into several categories:
 *
 * - {@see Exception\RequestException}: the request itself is invalid (e.g., missing URL).
 * - {@see Exception\NetworkException}: client-level network policy violation (e.g., denied destination).
 * - {@see Exception\ProtocolException}: the server sent a malformed or unparseable response.
 * - {@see Exception\TooManyRedirectsException}: redirect limit exceeded (when using {@see RedirectClient}).
 * - {@see Network\Exception\RuntimeException}: transport-level failure (connection refused, DNS failure, timeout).
 * - {@see IO\Exception\RuntimeException}: I/O error during the exchange (read/write failure on the socket).
 * - {@see Async\Exception\CancelledException}: the cancellation token fired during any stage.
 *
 * Transport-level exceptions ({@see \Psl\Network\Exception\RuntimeException},
 * {@see \Psl\IO\Exception\RuntimeException}) are allowed to propagate unwrapped
 * from the underlying transport packages.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
 *
 * @api
 */
interface ClientInterface
{
    /**
     * Send an HTTP request and return the transaction.
     *
     * Resolves the request URL (using the request's URL or the configuration's
     * base URL), establishes a connection, executes any registered middleware,
     * and performs the HTTP exchange. The returned {@see Transaction} contains the
     * final response, any preceding informational (1xx) responses, and any HTTP/2
     * server-pushed exchanges.
     *
     * If no configuration is provided, the client's default configuration is used.
     * The cancellation token is respected throughout all stages: URL resolution,
     * connection establishment, middleware execution, and the HTTP exchange.
     *
     * @param Request $request The HTTP request to send. Must have either a URL set directly or a request target resolvable against the configuration's base URL.
     * @param SendConfiguration $configuration Optional per-request configuration override.
     * @param CancellationTokenInterface $cancellation Token to cancel the operation at any stage.
     *
     * @throws Exception\RequestException If the request is invalid (e.g., no URL and no base URL configured).
     * @throws Exception\NetworkException If the connection is denied by a network policy (e.g., SSRF protection middleware).
     * @throws Exception\ProtocolException If the server sends a malformed or unparseable HTTP response.
     * @throws Exception\TooManyRedirectsException If the redirect limit is exceeded (when wrapped by {@see RedirectClient}).
     * @throws Exception\RuntimeException If the request fails due to a client-level error.
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the exchange (e.g., read/write failure on the underlying socket).
     * @throws Network\Exception\RuntimeException If a transport-level error occurs (e.g., connection refused, DNS resolution failure, connect timeout).
     * @throws Async\Exception\CancelledException If the cancellation token fires during any stage of the request lifecycle.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
     */
    public function send(
        Request $request,
        SendConfiguration $configuration = new SendConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction;
}
