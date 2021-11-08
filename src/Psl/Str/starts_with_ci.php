<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @pure
 */
function starts_with_ci(string $string, string $prefix, Encoding $encoding = Encoding::UTF_8): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search_ci($string, $prefix, 0, $encoding);
}
