<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * Returns whether the string ends with the given suffix.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
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
