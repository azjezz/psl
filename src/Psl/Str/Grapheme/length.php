<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

use function grapheme_strlen;

/**
 * Returns the length of the given string in grapheme units.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 */
function length(string $string): int
{
    $length = grapheme_strlen($string);

    if (null === $length || false === $length) {
        throw new Exception\InvalidArgumentException('$string is node made of grapheme clusters.');
    }

    return $length;
}
