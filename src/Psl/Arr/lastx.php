<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;

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
 *
 * @deprecated use `Iter\last` instead.
 *
 * @see Iter\last()
 */
function lastx(array $array)
{
    /**
     * @psalm-var Tk|null $last
     * @psalm-suppress DeprecatedFunction
     */
    $last = last_key($array);
    Psl\invariant(null !== $last, 'Expected a non-empty array.');

    return $array[$last];
}
