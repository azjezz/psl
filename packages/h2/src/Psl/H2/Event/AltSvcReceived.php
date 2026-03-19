<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when an ALTSVC frame is received from the server.
 *
 * Indicates that the server is advertising an alternative service endpoint
 * that the client can use for future requests to the same origin.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7838
 */
final readonly class AltSvcReceived implements EventInterface
{
    /**
     * @param int<0, max> $streamId The stream this applies to (0 for explicit origin).
     * @param string $origin The origin this alternative service applies to (non-empty when streamId is 0).
     * @param string $fieldValue The Alt-Svc field value (e.g. 'h3=":443"; ma=2592000').
     */
    public function __construct(
        public int $streamId,
        public string $origin,
        public string $fieldValue,
    ) {}
}
