<?php

declare(strict_types=1);

namespace Psl\Str;

use function error_clear_last;
use function error_reporting;

/**
 * Returns the given string as an integer, or null if the string isn't numeric.
 *
 * @pure
 */
function to_int(string $string): null|int
{
    error_clear_last();
    $previousLevel = error_reporting(0);
    try {
        $intValue = (int) $string;
    } finally {
        error_reporting($previousLevel);
    }

    if ((string) $intValue === $string) {
        return $intValue;
    }

    return null;
}
