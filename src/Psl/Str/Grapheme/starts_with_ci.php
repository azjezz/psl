<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @throws Psl\Exception\InvariantViolationException If unable to split $string into grapheme clusters.
 *
 * @pure
 */
function starts_with_ci(string $string, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search_ci($string, $prefix);
}
