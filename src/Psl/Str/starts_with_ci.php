<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function starts_with_ci(string $string, string $prefix, ?string $encoding = null): bool
{
    return 0 === search_ci($string, $prefix, 0, $encoding);
}
