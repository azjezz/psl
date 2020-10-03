<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Arr;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @psalm-return string Converts the character encoding of $string to $to_encoding from $from_encoding.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $from_encoding is invalid.
 * @throws Psl\Exception\InvariantViolationException If $to_encoding is invalid.
 * @throws Psl\Exception\InvariantViolationException If unable to convert $string form $from_encoding to $to_encoding.
 */
function convert_encoding(string $string, string $from_encoding, string $to_encoding): string
{
    Psl\invariant(in_array($from_encoding, mb_list_encodings(), true), '$from_encoding is invalid.');
    Psl\invariant(in_array($to_encoding, mb_list_encodings(), true), '$to_encoding is invalid.');

    $result = mb_convert_encoding($string, $to_encoding, $from_encoding);
    Psl\invariant(false !== $result, 'Unable to convert $string from $from_encoding to $to_encoding');

    return $result;
}
