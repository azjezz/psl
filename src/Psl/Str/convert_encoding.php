<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @psalm-return string Converts the character encoding of $string to $to_encoding from optionally $from_encoding.
 *
 * @psalm-pure
 */
function convert_encoding(string $string, string $from_encoding, string $to_encoding): ?string
{
    return mb_convert_encoding($string, $to_encoding, $from_encoding);
}
