<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;

/**
 * Retrieve a random value from a non-empty array.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $values
 *
 * @psalm-return Tv
 *
 * @throws Psl\Exception\InvariantViolationException If $values is empty.
 *
 * @deprecated use `Iter\random` instead.
 *
 * @see Iter\random()
 */
function random(array $values)
{
    return Iter\random($values);
}
