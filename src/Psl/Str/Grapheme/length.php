<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str\Exception;

use function grapheme_strlen;

/**
 * Returns the length of the given string in grapheme units.
 *
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 *
 * @return int<0, max>
 *
 * @pure
 *
 * @mago-ignore best-practices/no-boolean-literal-comparison
 */
function length(string $string): int
{
    $length = grapheme_strlen($string);

    if (null === $length || false === $length) {
        throw new Exception\InvalidArgumentException('$string is node made of grapheme clusters.');
    }

    return $length;
}
