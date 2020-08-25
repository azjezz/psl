<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * @psalm-pure
 */
function reverse(string $string): string
{
    for ($lo = 0, $hi = namespace\length($string) - 1; $lo < $hi; $lo++, $hi--) {
        $temp = $string[$lo];
        $string[$lo] = $string[$hi];
        $string[$hi] = $temp;
    }

    return $string;
}
