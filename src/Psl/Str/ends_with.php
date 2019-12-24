<?php

declare(strict_types=1);

namespace Psl\Str;

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
 */
function ends_with(
    string $string,
    string $suffix
): bool {
    if (is_empty($suffix)) {
        return true;
    }

    if (!\preg_match('//u', $suffix)) {
        return false;
    }

    return Byte\length($string) - Byte\length($suffix) === Byte\search_last($string, $suffix);
}
