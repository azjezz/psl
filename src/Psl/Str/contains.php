<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns whether the 'haystack' string contains the 'needle' string.
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string. If the offset is out-of-bounds, a ViolationException will be
 * thrown.
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
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If the $offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function contains(string $haystack, string $needle, int $offset = 0, ?string $encoding = null): bool
{
    if ('' === $needle) {
        Psl\Internal\validate_offset($offset, length($haystack, $encoding));

        return true;
    }

    return null !== search($haystack, $needle, $offset, $encoding);
}
