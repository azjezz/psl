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
 * @psalm-pure
 */
function fold(string $str): string
{
    foreach (Internal\CASE_FOLD as $k => $v) {
        $str = replace($str, $k, $v);
    }

    return lowercase($str);
}
