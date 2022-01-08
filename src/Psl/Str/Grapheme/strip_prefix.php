<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * Returns the string with the given prefix removed, or the string itself if
 * it doesn't start with the prefix.
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
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
