<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;

/**
 * Retrieve a random value from a non-empty array.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $values
 *
 * @throws Psl\Exception\InvariantViolationException If $values is empty.
 *
 * @return Tv
 *
 * @deprecated use `Iter\random` instead.
 * @see Iter\random()
 */
function random(array $values)
{
    return Iter\random($values);
}
