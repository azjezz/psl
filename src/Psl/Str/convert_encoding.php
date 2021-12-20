<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @pure
 */
function convert_encoding(string $string, Encoding $from_encoding, Encoding $to_encoding): string
{
    return mb_convert_encoding($string, $to_encoding->value, $from_encoding->value);
}
