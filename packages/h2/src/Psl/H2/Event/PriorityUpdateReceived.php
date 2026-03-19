<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a PRIORITY_UPDATE frame is received from the remote peer.
 *
 * PRIORITY_UPDATE frames use the extensible priority scheme defined in RFC 9218,
 * allowing clients to signal priority preferences using Structured Fields syntax.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9218
 */
final readonly class PriorityUpdateReceived implements EventInterface
{
    /**
     * @param int<1, max> $streamId The stream whose priority was updated.
     * @param string $fieldValue The Structured Fields serialized priority value (e.g. "u=0", "u=7, i").
     */
    public function __construct(
        public int $streamId,
        public string $fieldValue,
    ) {}
}
