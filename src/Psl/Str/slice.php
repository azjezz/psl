<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned. If the
 * offset is out-of-bounds, an InvariantViolationException will be thrown.
 */
function slice(string $string, int $offset, ?int $length = null): string
{
    Psl\invariant(null === $length || $length >= 0, 'Expected non-negative length.');
    $string_length = length($string);
    $offset = Psl\Internal\validate_offset($offset, $string_length);

    if (0 === $offset && (null === $length || $string_length <= $length)) {
        return $string;
    }

    return false === ($result = \mb_substr($string, $offset, $length, encoding($string))) ? '' : $result;
}
