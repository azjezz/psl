<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns whether the string ends with the given suffix (case-insensitive).
 *
 * Example:
 *
 *      Str\ends_with('Hello, World', 'd')
 *      => Bool(true)
 *
 *      Str\ends_with('Hello, World', 'D')
 *      => Bool(true)
 *
 *      Str\ends_with('Hello, World', 'World')
 *      => Bool(true)
 *
 *      Str\ends_with('Hello, World', 'world')
 *      => Bool(true)
 *
 *      Str\ends_with('Tunisia', 'e')
 *      => Bool(false)
 *
 *      Str\ends_with('تونس', 'س')
 *      => Bool(true)
 *
 *      Str\ends_with('تونس', 'ش')
 *      => Bool(false)
 */
function ends_with_ci(
    string $string,
    string $suffix
): bool {
    if ($suffix === $string) {
        return true;
    }

    $suffix_length = length($suffix);
    $total_length = length($string);
    if ($suffix_length > $total_length) {
        return false;
    }

    /** @psalm-suppress MissingThrowsDocblock - we don't supply $offset */
    $position = search_last_ci($string, $suffix);
    if (null === $position) {
        return false;
    }

    return $position + $suffix_length === $total_length;
}
