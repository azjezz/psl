<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 */
function strip_suffix(string $string, string $suffix): string
{
    if ('' === $suffix || !ends_with($string, $suffix)) {
        return $string;
    }

    /** @psalm-suppress MissingThrowsDocblock - we are sure that the $offset is positive */
    return slice($string, 0, length($string) - length($suffix));
}
