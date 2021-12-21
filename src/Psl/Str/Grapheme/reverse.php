<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Reverses the string.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If unable to split $string into grapheme clusters.
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
