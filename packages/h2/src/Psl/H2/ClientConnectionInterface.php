<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HPACK\Header;
use Psl\IO;

/**
 * Contract for a client-side HTTP/2 connection.
 *
 * Extends the base connection interface with client-specific operations:
 * stream priority signaling and extended CONNECT for protocol bootstrapping.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113
 */
interface ClientConnectionInterface extends ConnectionInterface
{
    /**
     * Send a PRIORITY frame to advise the server on stream scheduling.
     *
     * Deprecated in RFC 9113 but still supported for interoperability.
     *
     * @param positive-int $streamId The stream to prioritize.
     * @param int<0, max> $streamDependency The stream this one depends on (0 for root).
     * @param int<1, 256> $weight The priority weight (1-256, higher = more resources).
     * @param bool $exclusive Whether this is an exclusive dependency.
     *
     * @throws Exception\ProtocolException If the stream ID is invalid.
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.3
     */
    public function sendPriority(
        int $streamId,
        int $streamDependency = 0,
        int $weight = 16,
        bool $exclusive = false,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send a PRIORITY_UPDATE frame to signal stream priority using the
     * extensible priority scheme (RFC 9218).
     *
     * @param positive-int $streamId The stream to update priority for.
     * @param string $fieldValue The Structured Fields serialized priority
     *                           (e.g. "u=0" for urgent, "u=7, i" for low priority incremental).
     *
     * @throws Exception\ProtocolException If the stream ID is invalid.
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9218
     */
    public function sendPriorityUpdate(
        int $streamId,
        string $fieldValue,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send an extended CONNECT request to bootstrap a protocol over an H2 stream (RFC 8441).
     *
     * Opens a new stream with the CONNECT method and a :protocol pseudo-header,
     * enabling protocols like WebSocket to run over HTTP/2 streams. The server must
     * have advertised SETTINGS_ENABLE_CONNECT_PROTOCOL=1.
     *
     * After the server responds with 200, the stream becomes a bidirectional byte
     * tunnel - use sendData/readEvent to exchange protocol-specific data.
     *
     * @param positive-int $streamId The stream ID to use for the CONNECT request.
     * @param string $protocol The protocol to bootstrap (e.g. "websocket").
     * @param string $scheme The scheme (e.g. "https").
     * @param string $authority The authority (e.g. "example.com").
     * @param string $path The path (e.g. "/chat").
     * @param list<Header> $extraHeaders Additional headers (e.g. Origin, Sec-WebSocket-* headers).
     *
     * @throws Exception\RuntimeException For stream state errors or I/O failures.
     * @throws CancelledException If the cancellation token is cancelled.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8441
     */
    public function sendExtendedConnect(
        int $streamId,
        string $protocol,
        string $scheme,
        string $authority,
        string $path,
        array $extraHeaders = [],
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;
}
