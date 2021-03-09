<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

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
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If $multiplier is negative.
 */
function repeat(string $string, int $multiplier): string
{
    Psl\invariant($multiplier >= 0, 'Expected a non-negative multiplier');

    return str_repeat($string, $multiplier);
}
