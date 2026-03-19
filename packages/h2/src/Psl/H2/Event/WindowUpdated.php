<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a WINDOW_UPDATE frame is received from the remote peer.
 *
 * WINDOW_UPDATE frames are used to implement flow control in HTTP/2. The internal
 * flow-control window has already been adjusted by the time this event is emitted,
 * so consumers do not need to track window sizes manually.
 *
 * When {@see WindowUpdated::$streamId} is 0, the update applies to the connection-level
 * flow-control window. Otherwise, it applies to the specified stream's window.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.9 RFC 9113 Section 6.9 - WINDOW_UPDATE
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-5.2 RFC 9113 Section 5.2 - Flow Control
 */
final readonly class WindowUpdated implements EventInterface
{
    /**
     * @param int<0, max> $streamId The stream whose window was updated (0 for connection-level).
     * @param int<1, 2147483647> $increment The number of bytes added to the flow control window.
     */
    public function __construct(
        public int $streamId,
        public int $increment,
    ) {}
}
