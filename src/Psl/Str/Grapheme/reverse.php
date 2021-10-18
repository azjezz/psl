<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

/**
 * Reverses the string.
 *
 * @pure
 */
function reverse(string $string): string
{
    $reversed = '';
    $offset   = length($string);

    while ($offset-- > 0) {
        $reversed .= slice($string, $offset, 1);
    }

    return $reversed;
}
