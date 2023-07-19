<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strtoupper;

/**
 * Returns the string with all alphabetic characters converted to uppercase.
 *
 * @pure
 */
function uppercase(string $string, Encoding $encoding = Encoding::UTF_8): string
{
    return mb_strtoupper($string, $encoding->value);
}
