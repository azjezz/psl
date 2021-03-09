<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the string with the given prefix removed, or the string itself if
 * it doesn't start with the prefix.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function strip_prefix(string $string, string $prefix, ?string $encoding = null): string
{
    if ('' === $prefix || !starts_with($string, $prefix, $encoding)) {
        return $string;
    }

    return slice($string, length($prefix, $encoding), null, $encoding);
}
