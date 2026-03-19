<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HPACK\Header;
use Psl\IO;

/**
 * Contract for a server-side HTTP/2 connection.
 *
 * Extends the base connection interface with server-specific operations:
 * reading the client preface, sending response headers, and server push.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113
 */
interface ServerConnectionInterface extends ConnectionInterface
{
    /**
     * Read and validate the HTTP/2 client connection preface.
     *
     * Must be called before reading any frames on a server connection.
     *
     * @throws Exception\ConnectionException If the connection is closed before the preface is fully received.
     * @throws Exception\ProtocolException If the received preface does not match the expected value.
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readClientPreface(CancellationTokenInterface $cancellation = new NullCancellationToken()): void;

    /**
     * Send response HEADERS with a :status pseudo-header and additional headers.
     *
     * @param int<0, max> $streamId The stream to send the response on.
     * @param string $status The HTTP status code (e.g. "200", "404").
     * @param iterable<Header> $headers Response headers.
     * @param bool $endStream Whether this also ends the stream (no response body will follow).
     *
     * @throws Exception\RuntimeException For stream state errors, flow control errors, or I/O failures.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function sendHeadersWithStatus(
        int $streamId,
        string $status,
        iterable $headers,
        bool $endStream = false,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send a PUSH_PROMISE frame to initiate a server push.
     *
     * @param positive-int $associatedStreamId The client-initiated stream that triggered the push.
     * @param positive-int $promisedStreamId The server-assigned stream ID for the pushed resource.
     * @param list<Header> $headers Request headers for the promised resource.
     *
     * @throws Exception\RuntimeException For stream state errors, protocol errors, or I/O failures.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function sendPushPromise(
        int $associatedStreamId,
        int $promisedStreamId,
        array $headers,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send an ALTSVC frame to advertise an alternative service (RFC 7838).
     *
     * On stream 0, an explicit origin must be provided. On a non-zero stream,
     * the origin is empty and the frame applies to that stream's origin.
     *
     * @param int<0, max> $streamId 0 for explicit origin, non-zero for stream's origin.
     * @param string $origin The origin (required when streamId is 0, empty otherwise).
     * @param string $fieldValue The Alt-Svc field value (e.g. 'h3=":443"; ma=2592000').
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7838
     */
    public function sendAltSvc(
        int $streamId,
        string $origin,
        string $fieldValue,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send an ORIGIN frame to declare authoritative origins (RFC 8336).
     *
     * Enables connection coalescing - the client can reuse this connection
     * for requests to any of the declared origins.
     *
     * @param list<non-empty-string> $origins The origins this server is authoritative for.
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8336
     */
    public function sendOrigin(
        array $origins,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;
}
