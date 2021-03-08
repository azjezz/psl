<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the given string as an integer, or null if the string isn't numeric.
 *
 * @pure
 */
function to_int(string $string): ?int
{
    if ((string) (int) $string === $string) {
        return (int) $string;
    }

    return null;
}
