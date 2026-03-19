<?php

declare(strict_types=1);

namespace Psl\H2\Event;

use Psl\HPACK\Header;

/**
 * Emitted when a PUSH_PROMISE frame is received from a server.
 *
 * A PUSH_PROMISE allows a server to pre-emptively send a response for a request the
 * client has not yet made. The promised stream is placed in the "reserved (remote)"
 * state, and the server will later send HEADERS on the promised stream to begin the
 * pushed response.
 *
 * The {@see PushPromiseReceived::$headers} contain the decoded request pseudo-headers
 * and headers that describe the resource being pushed. Clients may cancel the promised
 * stream with RST_STREAM if the resource is not needed.
 *
 * Note: Push promises are only sent by servers and are deprecated in some HTTP/2
 * implementations. RFC 9113 permits endpoints to disable server push by setting
 * SETTINGS_ENABLE_PUSH to 0.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.6 RFC 9113 Section 6.6 - PUSH_PROMISE
 */
final readonly class PushPromiseReceived implements EventInterface
{
    /**
     * @param int<1, max> $streamId The stream on which the PUSH_PROMISE was sent.
     * @param int<1, max> $promisedStreamId The stream identifier reserved for the pushed response.
     * @param list<Header> $headers The decoded request headers for the promised resource.
     */
    public function __construct(
        public int $streamId,
        public int $promisedStreamId,
        public array $headers,
    ) {}
}
