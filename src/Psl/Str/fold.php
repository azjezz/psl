<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Internal;

/**
 * Perform case folding on a string.
 *
 * Example:
 *
 *      Str\fold('ẞ')
 *      => Str('ss')
 *
 * @pure
 */
function fold(string $string, Encoding $encoding = Encoding::UTF_8): string
{
    foreach (Internal\CASE_FOLD as $k => $v) {
        $string = replace($string, $k, $v, $encoding);
    }

    return lowercase($string, $encoding);
}
