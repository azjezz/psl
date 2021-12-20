<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_repeat;

/**
 * Returns the input string repeated `$multiplier` times.
 *
 * If the multiplier is 0, the empty string will be returned.
 *
 * Example:
 *
 *      Str\repeat('Go! ', 3)
 *      => Str('Go! Go! Go! ')
 *
 *      Str\repeat('?', 5)
 *      => Str('?????')
 *
 * @param int<0, max> $multiplier
 *
 * @pure
 */
function repeat(string $string, int $multiplier): string
{
    return str_repeat($string, $multiplier);
}
