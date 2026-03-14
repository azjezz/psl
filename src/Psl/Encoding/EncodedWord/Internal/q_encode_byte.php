<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function dechex;
use function ord;
use function str_pad;
use function strtoupper;

use const STR_PAD_LEFT;

/**
 * Q-encode a single byte per RFC 2047 §4.2.
 *
 * @internal
 */
function q_encode_byte(string $byte): string
{
    $ord = ord($byte);

    if ($byte === ' ') {
        return '_';
    }

    if ($ord >= 0x21 && $ord <= 0x7E && $byte !== '=' && $byte !== '?' && $byte !== '_') {
        return $byte;
    }

    return '=' . strtoupper(str_pad(dechex($ord), 2, '0', STR_PAD_LEFT));
}
