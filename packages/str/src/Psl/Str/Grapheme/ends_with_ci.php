<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * Returns whether the string ends with the given suffix (case-insensitive).
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 */
function ends_with_ci(string $string, string $suffix): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffixLength = namespace\length($suffix);
    $totalLength = namespace\length($string);
    if ($suffixLength > $totalLength) {
        return false;
    }

    $position = namespace\search_last_ci($string, $suffix);
    if (null === $position) {
        return false;
    }

    return ($position + $suffixLength) === $totalLength;
}
