<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strncmp;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @pure
 */
function starts_with(string $string, string $prefix): bool
{
    if ('' === $prefix) {
        return false;
    }

    return 0 === strncmp($string, $prefix, length($prefix));
}
