<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns whether the string ends with the given suffix.
 *
 * Example:
 *
 *      Str\ends_with('Hello, World', 'd')
 *      => Bool(true)
 *
 *      Str\ends_with('Hello, World', 'D')
 *      => Bool(false)
 *
 *      Str\ends_with('Hello, World', 'world')
 *      => Bool(false)
 *
 *      Str\ends_with('Hello, World', 'World')
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
function ends_with(string $string, string $suffix, ?string $encoding = null): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffix_length = length($suffix, $encoding);
    $total_length  = length($string, $encoding);
    if ($suffix_length > $total_length) {
        return false;
    }

    $position = search_last($string, $suffix, 0, $encoding);
    if (null === $position) {
        return false;
    }

    return $position + $suffix_length === $total_length;
}
