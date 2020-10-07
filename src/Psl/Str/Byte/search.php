<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;

use function strpos;

/**
 * Returns the first position of the 'needle' string in the 'haystack' string,
 * or null if it isn't found.
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string. If the offset is out-of-bounds, an InvariantViolationException will be
 * thrown.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $offset is out-of-bounds.
 */
function search(string $haystack, string $needle, int $offset = 0): ?int
{
    $offset = Psl\Internal\validate_offset($offset, length($haystack));

    if ('' === $needle) {
        return null;
    }

    return false === ($pos = strpos($haystack, $needle, $offset)) ? null : $pos;
}
