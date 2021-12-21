<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Str\Exception;

/**
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @pure
 */
function after_last(
    string $haystack,
    string $needle,
    int $offset = 0
): ?string {
    $position = search_last($haystack, $needle, $offset);
    if (null === $position) {
        return null;
    }

    $position += length($needle);

    return slice($haystack, $position);
}
