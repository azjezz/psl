<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Return the string with a slice specified by the offset/length replaced by the
 * given replacement string.
 *
 * If the length is omitted or exceeds the upper bound of the string, the
 * remainder of the string will be replaced. If the length is zero, the
 * replacement will be inserted at the offset.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If a negative $length is given.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function splice(
    string $string,
    string $replacement,
    int $offset = 0,
    ?int $length = null,
    ?string $encoding = null
): string {
    Psl\invariant(null === $length || $length >= 0, 'Expected a non-negative length.');
    $total_length = length($string, $encoding);
    $offset       = Psl\Internal\validate_offset($offset, $total_length);

    if (null === $length || ($offset + $length) >= $total_length) {
        return slice($string, 0, $offset, $encoding) . $replacement;
    }

    return slice($string, 0, $offset, $encoding) . $replacement . slice($string, $offset + $length, null, $encoding);
}
