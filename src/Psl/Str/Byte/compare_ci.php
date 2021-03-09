<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strcasecmp;
use function strncasecmp;

/**
 * Returns < 0 if `$string1` is less than `$string2`, > 0 if `$string1` is
 * greater than `$string2`, and 0 if they are equal (case-insensitive).
 *
 * @pure
 *
 * @param int|null $length number of characters to use in the comparison,
 *                         or null to compare the whole string
 */
function compare_ci(string $string, string $other, ?int $length = null): int
{
    return null === $length ?
        strcasecmp($string, $other) :
        strncasecmp($string, $other, $length);
}
