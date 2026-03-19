<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a PING frame is received from the remote peer.
 *
 * PING frames are a connection-level mechanism for measuring round-trip time or
 * verifying that a connection is still alive. If the received frame is not an ACK,
 * a PING ACK response containing the same opaque data has already been queued
 * automatically, so consumers do not need to respond manually.
 *
 * When {@see PingReceived::$ack} is true, this is a response to a PING that was
 * previously sent by the local endpoint.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.7 RFC 9113 Section 6.7 - PING
 */
final readonly class PingReceived implements EventInterface
{
    /**
     * @param string $opaqueData The 8-byte opaque data from the PING frame.
     * @param bool $ack Whether this is a PING ACK (response to a previously sent PING).
     */
    public function __construct(
        public string $opaqueData,
        public bool $ack,
    ) {}
}
