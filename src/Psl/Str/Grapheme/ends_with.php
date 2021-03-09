<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Returns whether the string ends with the given suffix.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If unable to convert $string to UTF-16,
 *                                                   or split it into graphemes.
 */
function ends_with(string $string, string $suffix): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffix_length = length($suffix);
    $total_length  = length($string);
    if ($suffix_length > $total_length) {
        return false;
    }

    /** @psalm-suppress MissingThrowsDocblock */
    $position = search_last($string, $suffix);
    if (null === $position) {
        return false;
    }

    return $position + $suffix_length === $total_length;
}
