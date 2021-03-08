<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * @throws Psl\Exception\InvariantViolationException If the $offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 *
 * @pure
 */
function before(
    string $haystack,
    string $needle,
    int $offset = 0,
    ?string $encoding = null
): ?string {
    $length = search($haystack, $needle, $offset, $encoding);
    if (null === $length) {
        return null;
    }

    return slice($haystack, 0, $length, $encoding);
}
