<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @pure
 */
function after_last_ci(string $haystack, string $needle, int $offset = 0, Encoding $encoding = Encoding::Utf8): ?string
{
    $position = search_last_ci($haystack, $needle, $offset, $encoding);
    if (null === $position) {
        return null;
    }

    $position += length($needle);

    return slice($haystack, $position, null, $encoding);
}
