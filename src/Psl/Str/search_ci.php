<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the first position of the 'needle' string in the 'haystack' string,
 * or null if it isn't found (case-insensitive).
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string.
 *
 * @throws Psl\Exception\InvariantViolationException If $offset is out-of-bounds.
 */
function search_ci(string $haystack, string $needle, int $offset = 0): ?int
{
    if ('' === $needle) {
        return null;
    }

    $offset = Psl\Internal\validate_offset($offset, length($haystack));

    return false === ($pos = \mb_stripos($haystack, $needle, $offset, encoding($haystack))) ? null : $pos;
}
