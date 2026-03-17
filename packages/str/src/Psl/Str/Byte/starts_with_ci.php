<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strncasecmp;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 *
 * @pure
 */
function starts_with_ci(string $string, string $prefix): bool
{
    if ('' === $prefix) {
        return false;
    }

    return 0 === strncasecmp($string, $prefix, length($prefix));
}
