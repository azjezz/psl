<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_stripos;

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
 * @throws Psl\Exception\InvariantViolationException If $offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function search_ci(string $haystack, string $needle, int $offset = 0, ?string $encoding = null): ?int
{
    if ('' === $needle) {
        return null;
    }

    $offset = Psl\Internal\validate_offset($offset, length($haystack, $encoding));

    return false === ($pos = mb_stripos($haystack, $needle, $offset, Internal\internal_encoding($encoding))) ?
        null :
        $pos;
}
