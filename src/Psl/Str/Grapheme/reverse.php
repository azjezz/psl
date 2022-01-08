<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * Reverses the string.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
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
