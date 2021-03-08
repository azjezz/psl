<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_substr;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned. If the
 * offset is out-of-bounds, an InvariantViolationException will be thrown.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If a negative $length is given.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function slice(string $string, int $offset, ?int $length = null, ?string $encoding = null): string
{
    Psl\invariant(null === $length || $length >= 0, 'Expected a non-negative length.');
    $string_length = length($string, $encoding);
    $offset        = Psl\Internal\validate_offset($offset, $string_length);

    if (0 === $offset && (null === $length || $string_length <= $length)) {
        return $string;
    }

    return (string) mb_substr($string, $offset, $length, Internal\internal_encoding($encoding));
}
