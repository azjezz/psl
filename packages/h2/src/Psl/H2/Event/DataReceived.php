<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a DATA frame is received on a stream.
 *
 * The data payload has already been stripped of any padding bytes. Flow-control
 * WINDOW_UPDATE frames are sent automatically to replenish the sender's window,
 * so consumers do not need to manage flow control manually.
 *
 * When {@see DataReceived::$endStream} is true, the remote peer has finished sending
 * on this stream and no further DATA or HEADERS frames will arrive for it.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.1 RFC 9113 Section 6.1 - DATA
 */
final readonly class DataReceived implements EventInterface
{
    /**
     * @param int<1, max> $streamId The stream this data belongs to.
     * @param string $data The application data payload (may be empty for END_STREAM-only frames).
     * @param bool $endStream Whether the END_STREAM flag was set, indicating the peer has finished sending.
     */
    public function __construct(
        public int $streamId,
        public string $data,
        public bool $endStream,
    ) {}
}
