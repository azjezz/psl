<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * @throws Psl\Exception\InvariantViolationException If the $offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 *
 * @psalm-pure
 */
function after_last(
    string $haystack,
    string $needle,
    int $offset = 0,
    ?string $encoding = null
): ?string {
    $position = search_last($haystack, $needle, $offset, $encoding);
    if (null === $position) {
        return null;
    }

    $position += length($needle);

    return slice($haystack, $position, null, $encoding);
}
