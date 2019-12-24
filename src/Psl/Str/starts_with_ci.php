<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 */
function starts_with_ci(string $str, string $prefix): bool
{
    return 0 === search_ci($str, $prefix);
}
