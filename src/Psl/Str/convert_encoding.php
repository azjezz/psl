<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_convert_encoding;

/**
 * Detect the encoding of the giving string.
 *
 * @psalm-return null|string The string encoding or null if unable to detect encoding.
 *
 * @psalm-pure
 */
function convert_encoding(string $string, string $from, string $to): ?string
{
    return mb_convert_encoding($string, $to, $from) ?: null;
}
