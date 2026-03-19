<?php

declare(strict_types=1);

namespace Psl\H2\Event;

use Psl\HPACK\Header;

/**
 * Emitted when a complete set of headers has been received and decoded.
 *
 * This event fires after all HEADERS and any subsequent CONTINUATION frames for a
 * stream have been reassembled and the HPACK-encoded header block has been decoded.
 * For request streams this carries the initial request pseudo-headers and headers;
 * for response streams this carries the response pseudo-headers and headers, or
 * trailing headers if received after DATA frames.
 *
 * When {@see HeadersReceived::$endStream} is true, the remote peer has finished
 * sending on this stream and no further DATA frames will follow.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.2 RFC 9113 Section 6.2 - HEADERS
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.10 RFC 9113 Section 6.10 - CONTINUATION
 */
final readonly class HeadersReceived implements EventInterface
{
    /**
     * @param int<1, max> $streamId The stream this header block belongs to.
     * @param list<Header> $headers The decoded headers.
     * @param bool $endStream Whether the END_STREAM flag was set, indicating no further DATA frames.
     */
    public function __construct(
        public int $streamId,
        public array $headers,
        public bool $endStream,
    ) {}
}
