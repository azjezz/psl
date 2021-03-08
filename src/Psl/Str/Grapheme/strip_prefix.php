<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

/**
 * Returns the string with the given prefix removed, or the string itself if
 * it doesn't start with the prefix.
 *
 * @pure
 */
function strip_prefix(string $string, string $prefix): string
{
    if ('' === $prefix || !starts_with($string, $prefix)) {
        return $string;
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return slice($string, length($prefix));
}
