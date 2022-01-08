<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

/**
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 * @throws Exception\InvalidArgumentException If $haystack is not made of grapheme clusters.
 *
 * @pure
 */
function before_ci(string $haystack, string $needle, int $offset = 0): ?string
{
    $length = search_ci($haystack, $needle, $offset);
    if (null === $length) {
        return null;
    }

    return slice($haystack, 0, $length);
}
