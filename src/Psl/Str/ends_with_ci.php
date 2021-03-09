<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

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
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function ends_with_ci(string $string, string $suffix, ?string $encoding = null): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffix_length = length($suffix, $encoding);
    $total_length  = length($string, $encoding);
    if ($suffix_length > $total_length) {
        return false;
    }

    $position = search_last_ci($string, $suffix, 0, $encoding);
    if (null === $position) {
        return false;
    }

    return $position + $suffix_length === $total_length;
}
