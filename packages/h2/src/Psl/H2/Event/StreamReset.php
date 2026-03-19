<?php

declare(strict_types=1);

namespace Psl\H2\Event;

use Psl\H2\ErrorCode;

/**
 * Emitted when a RST_STREAM frame is received, indicating abrupt stream termination.
 *
 * A RST_STREAM frame allows either endpoint to immediately terminate a stream,
 * typically due to an error condition or because the stream is no longer needed
 * (e.g., CANCEL). After this event, the stream transitions to the "closed" state
 * and a {@see StreamClosed} event is always emitted subsequently.
 *
 * The {@see StreamReset::$errorCode} indicates the reason for the reset; common
 * values include CANCEL, REFUSED_STREAM, and INTERNAL_ERROR.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.4 RFC 9113 Section 6.4 - RST_STREAM
 */
final readonly class StreamReset implements EventInterface
{
    /**
     * @param int<1, max> $streamId The reset stream identifier.
     * @param ErrorCode $errorCode The reason for the reset.
     */
    public function __construct(
        public int $streamId,
        public ErrorCode $errorCode,
    ) {}
}
