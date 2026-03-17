<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_convert_encoding;

/**
 * Convert character encoding of the giving string.
 *
 * @pure
 */
function convert_encoding(string $string, Encoding $fromEncoding, Encoding $toEncoding): string
{
    return (string) mb_convert_encoding($string, $toEncoding->value, $fromEncoding->value);
}
