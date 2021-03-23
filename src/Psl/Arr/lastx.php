<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;

/**
 * Get the last value of an array, If the array is empty, an InvariantViolationException
 * will be thrown.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 *
 * @throws Psl\Exception\InvariantViolationException If $array is empty.
 *
 * @return Tv
 *
 * @pure
 *
 * @deprecated use `Iter\last` instead.
 * @see Iter\last()
 */
function lastx(array $array)
{
    /**
     * @psalm-suppress DeprecatedFunction
     */
    $last = last_key($array);
    Psl\invariant(null !== $last, 'Expected a non-empty array.');

    return $array[$last];
}
