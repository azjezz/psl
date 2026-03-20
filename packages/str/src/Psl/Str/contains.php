<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_contains;

/**
 * Returns whether the 'haystack' string contains the 'needle' string.
 *
 * An optional offset determines where in the haystack the search begins.
 *
 * If the offset is negative, the search will begin that many characters from the end
 * of the string.
 *
 * Example:
 *
 *      Str\contains('hello', 'l')
 *      => Bool(true)
 *
 *      Str\contains('Hello, 'h')
 *      => Bool(false)
 *
 *      Str\contains('hello', 'L', 3)
 *      => Bool(false)
 *
 *      Str\contains('hello', 'l', 4)
 *      => Bool(false)
 *
 *      Str\contains('hello', 'l', 2)
 *      => Bool(true)
 *
 *      Str\contains('سيف', 'س')
 *      => Bool(true)
 *
 * @pure
 *
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 */
function contains(string $haystack, string $needle, int $offset = 0, Encoding $encoding = Encoding::Utf8): bool
{
    if ('' === $needle) {
        return Internal\validate_offset($offset, namespace\length($haystack, $encoding), true);
    }

    if (0 === $offset && ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8)) {
        return str_contains($haystack, $needle);
    }

    return null !== namespace\search($haystack, $needle, $offset, $encoding);
}
