<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_starts_with;

/**
 * Returns whether the string starts with the given prefix.
 *
 * @pure
 */
function starts_with(string $string, string $prefix, Encoding $encoding = Encoding::Utf8): bool
{
    if ('' === $prefix) {
        return false;
    }

    if ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8) {
        return str_starts_with($string, $prefix);
    }

    return 0 === namespace\search($string, $prefix, 0, $encoding);
}
