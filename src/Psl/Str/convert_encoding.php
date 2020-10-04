<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $from_encoding is invalid.
 * @throws Psl\Exception\InvariantViolationException If $to_encoding is invalid.
 * @throws Psl\Exception\InvariantViolationException If unable to convert $string form $from_encoding to $to_encoding.
 */
function convert_encoding(string $string, string $from_encoding, string $to_encoding): string
{
    Psl\invariant(Internal\is_encoding_valid($from_encoding), '$from_encoding is invalid.');
    Psl\invariant(Internal\is_encoding_valid($to_encoding), '$to_encoding is invalid.');

    $result = mb_convert_encoding($string, $to_encoding, $from_encoding);
    Psl\invariant(false !== $result, 'Unable to convert $string from $from_encoding to $to_encoding.');

    return $result;
}
