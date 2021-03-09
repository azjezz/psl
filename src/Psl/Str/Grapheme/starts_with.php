<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @pure
 */
function starts_with(string $string, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search($string, $prefix);
}
