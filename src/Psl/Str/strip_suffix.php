<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function strip_suffix(string $string, string $suffix, ?string $encoding = null): string
{
    if ('' === $suffix || !ends_with($string, $suffix, $encoding)) {
        return $string;
    }

    return slice($string, 0, length($string, $encoding) - length($suffix, $encoding), $encoding);
}
