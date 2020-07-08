<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns whether the string ends with the given suffix.
 */
function ends_with(
    string $string,
    string $suffix
): bool {
    /** @psalm-suppress MissingThrowsDocblock - we don't supply $offset */
    if (null === search($string, $suffix)) {
        return false;
    }

    $suffix_length = length($suffix);

    /** @psalm-suppress MissingThrowsDocblock - $offset is within-bounds. */
    return slice($string, -$suffix_length) === $suffix;
}
