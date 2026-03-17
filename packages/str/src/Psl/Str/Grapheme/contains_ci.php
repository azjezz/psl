<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str;

/**
 * Returns whether the 'haystack' string contains the 'needle' string.
 *
 * An optional offset determines where in the haystack the search begins.
 *
 * If the offset is negative, the search will begin that many characters from the end
 * of the string.
 *
 * @pure
 *
 * @throws Str\Exception\OutOfBoundsException If $offset is out-of-bounds.
 * @throws Str\Exception\InvalidArgumentException If $haystack is not made of grapheme clusters.
 */
function contains_ci(string $haystack, string $needle, int $offset = 0): bool
{
    if ('' === $needle) {
        return Str\Internal\validate_offset($offset, length($haystack), true);
    }

    return null !== search_ci($haystack, $needle, $offset);
}
