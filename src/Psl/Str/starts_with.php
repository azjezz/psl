<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @pure
 */
function starts_with(string $string, string $prefix, Encoding $encoding = Encoding::UTF_8): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return 0 === search($string, $prefix, 0, $encoding);
}
