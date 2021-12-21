<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Str;

use function substr;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned.
 *
 * @param int<0, max> $length
 *
 * @pure
 *
 * @throws Str\Exception\OutOfBoundsException If $offset is out-of-bounds.
 */
function slice(string $string, int $offset, ?int $length = null): string
{
    $offset = Str\Internal\validate_offset($offset, length($string));

    return null === $length ? substr($string, $offset) : substr($string, $offset, $length);
}
