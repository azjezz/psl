<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @psalm-pure
 */
function starts_with(string $str, string $prefix): bool
{
    /** @psalm-suppress MissingThrowsDocblock - we don't supply $offset */
    return 0 === search($str, $prefix);
}
