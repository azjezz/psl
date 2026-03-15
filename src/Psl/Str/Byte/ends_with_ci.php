<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function substr_compare;

/**
 * Returns whether the string ends with the given suffix (case-insensitive).
 *
 * @pure
 */
function ends_with_ci(string $string, string $suffix): bool
{
    if (null === search_ci($string, $suffix)) {
        return false;
    }

    $suffixLength = length($suffix);

    return (
        length($string) >= $suffixLength
        && 0 === substr_compare($string, $suffix, -$suffixLength, $suffixLength, true)
    );
}
