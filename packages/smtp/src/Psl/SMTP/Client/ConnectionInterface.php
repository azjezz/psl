<?php

declare(strict_types=1);

namespace Psl\SMTP\Client;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\Network;
use Psl\SMTP\Capability;
use Psl\SMTP\Command;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\PossibleAttackException;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Reply;
use Psl\TLS;

/**
 * An SMTP client connection that is both a network stream and an SMTP protocol speaker.
 *
 * Extends {@see Network\StreamInterface} so the connection can be used as a raw
 * read/write stream for custom protocol extensions (e.g. DATA body streaming).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5321
 *
 * @api
 */
interface ConnectionInterface extends Network\StreamInterface
{
    /**
     * The maximum message size advertised by the server, or null if not advertised.
     */
    public null|int $maxSize { get; }

    /**
     * Check whether the server advertised a given ESMTP capability.
     */
    public function supportsCapability(string|Capability $capability): bool;

    /**
     * Get the parameter string for a given ESMTP capability, or null if not advertised.
     *
     * For example, if the server advertised "AUTH PLAIN LOGIN", calling
     * getCapabilityValue('AUTH') returns "PLAIN LOGIN".
     */
    public function getCapabilityValue(string|Capability $capability): null|string;

    /**
     * Read and validate the server greeting (must be 220).
     *
     * @throws ConnectionException If the greeting indicates a temporary or permanent failure.
     * @throws ProtocolException If the greeting is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function readGreeting(CancellationTokenInterface $cancellation = new NullCancellationToken()): Reply;

    /**
     * Send HELO to identify the client.
     *
     * HELO is the original SMTP greeting per RFC 821. It does not advertise
     * extended capabilities. Use {@see ehlo()} instead unless the server
     * does not support ESMTP.
     *
     * @throws ProtocolException If the reply is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function helo(
        string $hostname,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Reply;

    /**
     * Send EHLO and parse the capability advertisement.
     *
     * Updates the internal capability set and max message size from the reply.
     *
     * @throws ProtocolException If the reply is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function ehlo(
        string $hostname,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Reply;

    /**
     * Upgrade the connection to TLS via STARTTLS.
     *
     * Sends the STARTTLS command, reads the 220 reply, performs the TLS handshake,
     * and replaces the underlying stream with the TLS-wrapped equivalent.
     * A new EHLO should be sent after this to refresh capabilities.
     *
     * @throws ConnectionException If the TLS upgrade fails or the server rejects STARTTLS.
     * @throws ProtocolException If the reply is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function startTls(
        TLS\Connector $connector,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Send an SMTP command and read the reply.
     *
     * @throws PossibleAttackException If the command contains CRLF or null bytes.
     * @throws ProtocolException If the reply is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function sendCommand(
        string|Command $command,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Reply;

    /**
     * Send an SMTP command without reading a reply.
     *
     * Used for pipelining, where multiple commands are sent before reading replies.
     *
     * @throws PossibleAttackException If the command contains CRLF or null bytes.
     * @throws IO\Exception\RuntimeException If the write fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function writeCommand(
        string|Command $command,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;

    /**
     * Read a single SMTP reply from the connection.
     *
     * @throws ProtocolException If the reply is malformed.
     * @throws IO\Exception\RuntimeException If the underlying IO operation fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function readReply(CancellationTokenInterface $cancellation = new NullCancellationToken()): Reply;
}
