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
    if ('' === $suffix) {
        return true;
    }

    if (!\preg_match('//u', $suffix)) {
        return false;
    }

    return (bool) \preg_match('{' . \preg_quote($suffix, '/') . '$}iu', $string);
}
