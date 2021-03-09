<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If either one of $from_encoding and $to_encoding is invalid.
 */
function convert_encoding(string $string, string $from_encoding, string $to_encoding): string
{
    Psl\invariant(Internal\is_encoding_valid($from_encoding), '$from_encoding is invalid.');
    Psl\invariant(Internal\is_encoding_valid($to_encoding), '$to_encoding is invalid.');

    return mb_convert_encoding($string, $to_encoding, $from_encoding);
}
