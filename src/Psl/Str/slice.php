<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_substr;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned. If the
 * offset is out-of-bounds, an `Exception\OutOfBoundsException` will be thrown.
 *
 * @param null|int<0, max> $length
 *
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 *
 * @pure
 */
function slice(string $string, int $offset, ?int $length = null, Encoding $encoding = Encoding::UTF_8): string
{
    $string_length = length($string, $encoding);
    $offset        = Internal\validate_offset($offset, $string_length);
    if (0 === $offset && (null === $length || $string_length <= $length)) {
        return $string;
    }

    return mb_substr($string, $offset, $length, $encoding->value);
}
