<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @pure
 */
function strip_suffix(string $string, string $suffix, Encoding $encoding = Encoding::UTF_8): string
{
    if ('' === $suffix || !ends_with($string, $suffix, $encoding)) {
        return $string;
    }

    return slice($string, 0, length($string, $encoding) - length($suffix, $encoding), $encoding);
}
