<?php

declare(strict_types=1);

namespace Psl\UDP\Internal;

use Psl\Network;

use function strlen;

/**
 * Maximum IPv4 UDP payload size (65535 - 20 IP header - 8 UDP header).
 */
const MAX_DATAGRAM_SIZE = 65_507;

/**
 * Validate that the payload does not exceed the maximum UDP datagram size.
 *
 * @internal
 *
 * @throws Network\Exception\InvalidArgumentException If the payload is too large.
 */
function validate_payload_size(string $data): void
{
    if (strlen($data) > MAX_DATAGRAM_SIZE) {
        throw new Network\Exception\InvalidArgumentException(
            'UDP datagram payload exceeds maximum size of ' . MAX_DATAGRAM_SIZE . ' bytes.',
        );
    }
}
