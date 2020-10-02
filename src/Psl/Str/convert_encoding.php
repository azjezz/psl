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
 */
function convert_encoding(string $string, string $from_encoding, string $to_encoding): ?string
{
    Psl\invariant(Arr\contains(mb_list_encodings(), $from_encoding), '$from_encoding is invalid.');
    Psl\invariant(Arr\contains(mb_list_encodings(), $to_encoding), '$to_encoding is invalid.');

    return mb_convert_encoding($string, $to_encoding, $from_encoding);
}
