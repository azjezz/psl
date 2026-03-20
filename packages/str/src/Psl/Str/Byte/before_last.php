<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Str\Exception;

/**
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @pure
 */
function before_last(string $haystack, string $needle, int $offset = 0): null|string
{
    $length = namespace\search_last($haystack, $needle, $offset);
    if (null === $length) {
        return null;
    }

    return namespace\slice($haystack, 0, $length);
}
