<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @pure
 */
function starts_with_ci(string $string, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search_ci($string, $prefix);
}
