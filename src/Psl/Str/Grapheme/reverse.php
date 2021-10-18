<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

/**
 * Reverses the string.
 *
 * @pure
 *
 * @throws \Psl\Exception\InvariantViolationException If unable to convert $string to UTF-16,
 *                                                    or split it into graphemes
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
