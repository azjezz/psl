<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns whether the string starts with the given prefix (case-insensitive).
 */
function starts_with_ci(string $str, string $prefix): bool
{
    if ('' === $prefix) {
        return false;
    }

    return 0 === \strncasecmp($str, $prefix, length($prefix));
}
