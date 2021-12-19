<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the given prefix removed, or the string itself if
 * it doesn't start with the prefix.
 *
 * @pure
 */
function strip_prefix(string $string, string $prefix, Encoding $encoding = Encoding::UTF_8): string
{
    if ($prefix === $string) {
        return '';
    }

    if ('' === $prefix || '' === $string || !starts_with($string, $prefix, $encoding)) {
        return $string;
    }

    return slice($string, length($prefix, $encoding), null, $encoding);
}
