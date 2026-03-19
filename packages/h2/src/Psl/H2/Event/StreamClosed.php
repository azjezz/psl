<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a stream transitions to the "closed" state.
 *
 * A stream enters the closed state after both endpoints have sent frames with the
 * END_STREAM flag set, or after either endpoint sends a RST_STREAM frame. In the
 * latter case, this event is always preceded by a {@see StreamReset} event.
 *
 * Once closed, the stream identifier cannot be reused and all resources associated
 * with the stream may be released.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-5.1 RFC 9113 Section 5.1 - Stream States
 */
final readonly class StreamClosed implements EventInterface
{
    /**
     * @param int<1, max> $streamId The closed stream identifier.
     */
    public function __construct(
        public int $streamId,
    ) {}
}
