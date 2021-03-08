<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the string.
 *
 * If the length is zero, the empty string will be returned.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If a negative $length is given, or $offset is out-of-bounds.
 */
function slice(string $string, int $offset, ?int $length = null): string
{
    Psl\invariant(null === $length || $length >= 0, 'Expected a non-negative length.');
    $string_length = length($string);
    $offset        = Psl\Internal\validate_offset($offset, $string_length);

    if (0 === $offset && (null === $length || $string_length <= $length)) {
        return $string;
    }

    if (null === $length) {
        return (string) grapheme_substr($string, $offset);
    }

    return (string) grapheme_substr($string, $offset, $length);
}
