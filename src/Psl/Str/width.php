<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strwidth;

/**
 * Return width of length.
 *
 * @pure
 */
function width(string $string, Encoding $encoding = Encoding::UTF_8): int
{
    return mb_strwidth($string, $encoding->value);
}
