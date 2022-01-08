<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 *
 * @pure
 */
function starts_with_ci(string $string, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search_ci($string, $prefix);
}
