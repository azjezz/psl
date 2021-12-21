<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If unable to split $string into grapheme clusters.
 */
function starts_with(string $string, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search($string, $prefix);
}
