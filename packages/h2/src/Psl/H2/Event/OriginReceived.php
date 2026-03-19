<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when an ORIGIN frame is received from the server.
 *
 * Indicates the set of origins the server considers itself authoritative for,
 * enabling the client to coalesce requests to those origins onto this connection
 * without additional TLS handshakes.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8336
 */
final readonly class OriginReceived implements EventInterface
{
    /**
     * @param list<non-empty-string> $origins The origins the server is authoritative for.
     */
    public function __construct(
        public array $origins,
    ) {}
}
