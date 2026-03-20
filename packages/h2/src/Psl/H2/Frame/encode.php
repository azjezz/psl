<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

use function pack;
use function strlen;

/**
 * Encode a RawFrame into a binary string for transmission.
 *
 * Produces the 9-byte frame header (length, type, flags, stream ID) followed
 * by the payload bytes, as defined by the HTTP/2 frame format.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-4.1
 */
function encode(RawFrame $frame): string
{
    $length = strlen($frame->payload);

    return pack('NCN', ($length << 8) | $frame->type, $frame->flags, $frame->streamId & 0x7FFF_FFFF) . $frame->payload;
}
