<?php

declare(strict_types=1);

namespace Psl\H2\Event;

use Psl\H2\ErrorCode;

/**
 * Emitted when a GOAWAY frame is received, indicating graceful connection shutdown.
 *
 * After receiving this event, no new streams should be initiated on the connection.
 * Streams with identifiers up to and including {@see GoAwayReceived::$lastStreamId}
 * may still complete normally; streams above that identifier were not processed by the
 * peer and should be retried on a new connection if needed.
 *
 * The {@see GoAwayReceived::$debugData} field may contain human-readable diagnostic
 * information useful for logging, but its contents are opaque to the protocol.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.8 RFC 9113 Section 6.8 - GOAWAY
 */
final readonly class GoAwayReceived implements EventInterface
{
    /**
     * @param int<0, max> $lastStreamId The highest stream ID the peer will process.
     * @param ErrorCode $errorCode The reason for the shutdown.
     * @param string $debugData Optional diagnostic data (opaque to the protocol).
     */
    public function __construct(
        public int $lastStreamId,
        public ErrorCode $errorCode,
        public string $debugData,
    ) {}
}
