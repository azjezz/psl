<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;

/**
 * Get the first value of an array, If the array is empty, an InvariantViolationException
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
 * @deprecated use `Iter\first` instead.
 *
 * @see Iter\first()
 */
function firstx(array $array)
{
    /**
     * @psalm-var Tk|null $first
     * @psalm-suppress DeprecatedFunction
     */
    $first = first_key($array);
    Psl\invariant(null !== $first, 'Expected a non-empty array.');

    return $array[$first];
}
