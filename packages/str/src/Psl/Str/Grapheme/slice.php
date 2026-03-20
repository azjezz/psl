<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Str;

use function grapheme_substr;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the string.
 *
 * If the length is zero, the empty string will be returned.
 *
 * @param null|int<0, max> $length
 *
 * @pure
 *
 * @throws Str\Exception\OutOfBoundsException If $offset is out-of-bounds.
 * @throws Str\Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 */
function slice(string $string, int $offset, null|int $length = null): string
{
    $stringLength = namespace\length($string);
    $offset = Str\Internal\validate_offset($offset, $stringLength);

    if (0 === $offset && (null === $length || $stringLength <= $length)) {
        return $string;
    }

    if (null === $length) {
        return (string) grapheme_substr($string, $offset);
    }

    return (string) grapheme_substr($string, $offset, $length);
}
