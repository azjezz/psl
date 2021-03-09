<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strcmp;
use function strncmp;

/**
 * Returns < 0 if `$string1` is less than `$string2`, > 0 if `$string1` is
 * greater than `$string2`, and 0 if they are equal.
 *
 * @param int|null $length number of characters to use in the comparison,
 *                         or null to compare the whole string
 *
 * @pure
 */
function compare(string $string, string $other, ?int $length = null): int
{
    return null === $length ?
        strcmp($string, $other) :
        strncmp($string, $other, $length);
}
