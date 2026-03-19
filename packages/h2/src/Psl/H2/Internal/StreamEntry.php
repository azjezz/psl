<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use Psl\H2\StreamState;

use const Psl\H2\DEFAULT_INITIAL_WINDOW_SIZE;

/**
 * Per-stream state tracking.
 *
 * @internal
 */
final class StreamEntry
{
    /**
     * Current state of this stream in the HTTP/2 lifecycle.
     */
    public StreamState $state = StreamState::Idle;

    /**
     * Remaining send flow control window size in bytes.
     */
    public int $sendWindow;

    /**
     * Remaining receive flow control window size in bytes.
     */
    public int $receiveWindow;

    /**
     * Whether initial HEADERS have been received on this stream.
     */
    public bool $receivedHeaders = false;

    public function __construct(
        int $initialSendWindow = DEFAULT_INITIAL_WINDOW_SIZE,
        int $initialReceiveWindow = DEFAULT_INITIAL_WINDOW_SIZE,
    ) {
        $this->sendWindow = $initialSendWindow;
        $this->receiveWindow = $initialReceiveWindow;
    }
}
