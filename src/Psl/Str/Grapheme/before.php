<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * @throws Psl\Exception\InvariantViolationException If the $offset is out-of-bounds.
 *
 * @pure
 */
function before(
    string $haystack,
    string $needle,
    int $offset = 0
): ?string {
    $length = search($haystack, $needle, $offset);
    if (null === $length) {
        return null;
    }

    return slice($haystack, 0, $length);
}
