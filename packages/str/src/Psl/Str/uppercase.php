<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strtoupper;

/**
 * Returns the string with all alphabetic characters converted to uppercase.
 *
 * @pure
 *
 * @api
 *
 * @return ($string is non-empty-string ? non-empty-uppercase-string : uppercase-string)
 */
function uppercase(string $string, Encoding $encoding = Encoding::Utf8): string
{
    return mb_strtoupper($string, $encoding->value);
}
