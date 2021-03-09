<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_strripos;

/**
 * Returns the last position of the 'needle' string in the 'haystack' string,
 * or null if it isn't found (case-insensitive).
 *
 * An optional offset determines where in the haystack (from the beginning) the
 * search begins. If the offset is negative, the search will begin that many
 * characters from the end of the string and go backwards.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If the offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function search_last_ci(string $haystack, string $needle, int $offset = 0, ?string $encoding = null): ?int
{
    if ('' === $needle) {
        return null;
    }

    $haystack_length = length($haystack, $encoding);
    Psl\invariant($offset >= -$haystack_length && $offset <= $haystack_length, 'Offset is out-of-bounds.');

    return false === ($pos = mb_strripos($haystack, $needle, $offset, Internal\internal_encoding($encoding))) ?
        null :
        $pos;
}
