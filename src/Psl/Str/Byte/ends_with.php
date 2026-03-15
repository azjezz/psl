<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns whether the string ends with the given suffix.
 *
 * @pure
 */
function ends_with(string $string, string $suffix): bool
{
    if (null === search($string, $suffix)) {
        return false;
    }

    $suffixLength = length($suffix);

    return slice($string, -$suffixLength) === $suffix;
}
