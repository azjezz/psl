<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @psalm-pure
 */
function starts_with(string $str, string $prefix): bool
{
    if ('' === $prefix) {
        return false;
    }

    return 0 === \strncmp($str, $prefix, length($prefix));
}
