<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_shuffle;

/**
 * Randomly shuffles a string.
 *
 * @pure
 */
function shuffle(string $string): string
{
    if (namespace\length($string) < 1) {
        return $string;
    }

    return str_shuffle($string);
}
