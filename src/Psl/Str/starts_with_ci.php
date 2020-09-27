<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function starts_with_ci(string $str, string $prefix, ?string $encoding = null): bool
{
    return 0 === search_ci($str, $prefix, 0, $encoding);
}
