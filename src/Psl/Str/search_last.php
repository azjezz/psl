<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strrpos;

/**
 * Returns the last position of the 'needle' string in the 'haystack' string,
 * or null if it isn't found.
 *
 * An optional offset determines where in the haystack (from the beginning) the
 * search begins. If the offset is negative, the search will begin that many
 * characters from the end of the string and go backwards.
 *
 * @pure
 *
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @return null|int<0, max>
 */
function search_last(string $haystack, string $needle, int $offset = 0, Encoding $encoding = Encoding::UTF_8): ?int
{
    if ('' === $needle) {
        return null;
    }

    $offset = Internal\validate_offset($offset, length($haystack, $encoding));

    /** @var null|int<0, max> */
    return false === ($pos = mb_strrpos($haystack, $needle, $offset, $encoding->value)) ?
        null :
        $pos;
}
