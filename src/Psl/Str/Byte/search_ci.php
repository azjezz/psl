<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Str;

use function stripos;

/**
 * Returns the first position of the 'needle' string in the 'haystack' string,
 * or null if it isn't found (case-insensitive).
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string.
 *
 * @pure
 *
 * @throws Str\Exception\OutOfBoundsException If $offset is out-of-bounds.
 *
 * @return null|int<0, max>
 */
function search_ci(string $haystack, string $needle, int $offset = 0): ?int
{
    $offset = Str\Internal\validate_offset($offset, length($haystack));

    if ('' === $needle) {
        return null;
    }

    /** @var null|int<0, max> */
    return false === ($pos = stripos($haystack, $needle, $offset)) ? null : $pos;
}
