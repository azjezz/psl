<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Get the first value of an array, If the array is empty, an InvariantViolationException
 * will be thrown.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return Tv
 *
 * @psalm-pure
 */
function firstx(array $array)
{
    /** @psalm-var Tk|null $first */
    $first = first_key($array);
    Psl\invariant(null !== $first, 'Expected a non-empty array.');

    /** @psalm-var Tv */
    return at($array, $first);
}
