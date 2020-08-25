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
 *
 * @psalm-pure
 */
function ends_with(string $string, string $suffix): bool
{
    if ($suffix === $string) {
        return true;
    }

    $suffix_length = length($suffix);
    $total_length = length($string);
    if ($suffix_length > $total_length) {
        return false;
    }

    /** @psalm-suppress MissingThrowsDocblock - we don't supply $offset */
    $position = search_last($string, $suffix);
    if (null === $position) {
        return false;
    }

    return $position + $suffix_length === $total_length;
}
