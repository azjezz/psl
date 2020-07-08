<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Get the last value of an array, If the array is empty, an InvariantViolationException
 * will be thrown.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return Tv
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $array is empty.
 */
function lastx(array $array)
{
    /** @psalm-var null|Tk $last */
    $last = last_key($array);
    Psl\invariant(null !== $last, 'Expected a non-empty array.');

    /** @psalm-var Tv */
    return at($array, $last);
}
