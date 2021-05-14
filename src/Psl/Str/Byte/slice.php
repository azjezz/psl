<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;

use function substr;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If $length is negative, or the $offset is out-of-bounds.
 */
function slice(string $string, int $offset, ?int $length = null): string
{
    Psl\invariant(null === $length || $length >= 0, 'Expected a non-negative length.');
    $offset = Psl\Internal\validate_offset($offset, length($string));

    return null === $length ? substr($string, $offset) : substr($string, $offset, $length);
}
