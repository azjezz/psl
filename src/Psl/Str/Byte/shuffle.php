<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Randomly shuffles a string.
 *
 * @psalm-pure
 */
function shuffle(string $string): string
{
    if (length($string) < 1) {
        return $string;
    }

    return \str_shuffle($string);
}
