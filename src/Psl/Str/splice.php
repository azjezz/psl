<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Return the string with a slice specified by the offset/length replaced by the
 * given replacement string.
 *
 * If the length is omitted or exceeds the upper bound of the string, the
 * remainder of the string will be replaced. If the length is zero, the
 * replacement will be inserted at the offset.
 *
 * @param null|int<0, max> $length
 *
 * @pure
 *
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 */
function splice(
    string $string,
    string $replacement,
    int $offset = 0,
    ?int $length = null,
    Encoding $encoding = Encoding::UTF_8
): string {
    $total_length = length($string, $encoding);
    $offset       = Internal\validate_offset($offset, $total_length);

    if (null === $length || ($offset + $length) >= $total_length) {
        return slice($string, 0, $offset, $encoding) . $replacement;
    }

    return slice($string, 0, $offset, $encoding) . $replacement . slice($string, $offset + $length, null, $encoding);
}
