<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Perform case folding on a string.
 *
 * Example:
 *
 *      Str\fold('ẞ')
 *      => Str('ss')
 *
 * @return lowercase-string
 *
 * @pure
 */
function fold(string $string, Encoding $encoding = Encoding::Utf8): string
{
    foreach (Internal\CASE_FOLD as $k => $v) {
        $string = namespace\replace($string, $k, $v, $encoding);
    }

    return namespace\lowercase($string, $encoding);
}
