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
    /** @psalm-suppress MissingThrowsDocblock - we don't supply $offset. */
    if (null === search_ci($string, $suffix)) {
        return false;
    }

    $suffix_length = length($suffix);

    return length($string) >= $suffix_length &&
        0 === substr_compare($string, $suffix, -$suffix_length, $suffix_length, true);
}
