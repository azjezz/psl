<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * @pure
 */
function reverse(string $string): string
{
    $lo = 0;
    $hi = namespace\length($string) - 1;

    for (; $lo < $hi; $lo++, $hi--) {
        $temp        = $string[$lo];
        $string[$lo] = $string[$hi];
        $string[$hi] = $temp;
    }

    return $string;
}
