<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * @throws Psl\Exception\InvariantViolationException If the $offset is out-of-bounds.
 *
 * @pure
 */
function before_ci(
    string $haystack,
    string $needle,
    int $offset = 0,
    Encoding $encoding = Encoding::UTF_8
): ?string {
    $length = search_ci($haystack, $needle, $offset, $encoding);
    if (null === $length) {
        return null;
    }

    return slice($haystack, 0, $length, $encoding);
}
