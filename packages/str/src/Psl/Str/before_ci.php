<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @pure
 */
function before_ci(string $haystack, string $needle, int $offset = 0, Encoding $encoding = Encoding::Utf8): null|string
{
    $length = namespace\search_ci($haystack, $needle, $offset, $encoding);
    if (null === $length) {
        return null;
    }

    return namespace\slice($haystack, 0, $length, $encoding);
}
