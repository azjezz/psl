<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;
use Psl\Str;

use function grapheme_strpos;

/**
 * Returns the first position of the 'needle' string in the 'haystack' string
 * grapheme units, or null if it isn't found.
 *
 * An optional offset determines where in the haystack the search begins.
 *
 * If the offset is negative, the search will begin that many characters from the end
 * of the string.
 *
 * @pure
 *
 * @throws Str\Exception\OutOfBoundsException If $offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If unable to split $string into grapheme clusters.
 *
 * @return null|int<0, max>
 */
function search(string $haystack, string $needle, int $offset = 0): ?int
{
    if ('' === $needle) {
        return null;
    }

    $offset = Str\Internal\validate_offset($offset, length($haystack));

    /** @var null|int<0, max> */
    return false === ($pos = grapheme_strpos($haystack, $needle, $offset)) ?
        null :
        $pos;
}
