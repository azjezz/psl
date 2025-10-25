<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Internal;

/**
 * Returns the given string as an integer, or null if the string isn't numeric.
 *
 * @pure
 */
function to_int(string $string): null|int
{
    // Prevent Deprecated: float-string "1e123" is not representable as an int, cast occurred.
    $int_value = Internal\suppress(static fn(): int => (int) $string);

    if ((string) $int_value === $string) {
        return $int_value;
    }

    return null;
}
