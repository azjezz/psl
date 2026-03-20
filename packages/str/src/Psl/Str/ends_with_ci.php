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
 *
 * @pure
 */
function ends_with_ci(string $string, string $suffix, Encoding $encoding = Encoding::Utf8): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffixLength = namespace\length($suffix, $encoding);
    $totalLength = namespace\length($string, $encoding);
    if ($suffixLength > $totalLength) {
        return false;
    }

    $position = namespace\search_last_ci($string, $suffix, 0, $encoding);
    if (null === $position) {
        return false;
    }

    return ($position + $suffixLength) === $totalLength;
}
