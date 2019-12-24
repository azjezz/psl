<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns whether the string ends with the given suffix.
 */
function ends_with(
    string $string,
    string $suffix
): bool {
    return 0 === ($suffix_length = length($suffix)) || (length($string) >= $suffix_length && 0 === \substr_compare($string, $suffix, -$suffix_length, $suffix_length));
}
