<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\PseudoRandom;
use Psl\Vec;

/**
 * Retrieve a random value from a non-empty iterable.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Tv
 *
 * @throws Psl\Exception\InvariantViolationException If $iterable is empty.
 */
function random(iterable $iterable)
{
    // We convert the iterable to an array before checking if it is empty,
    // this helps us avoids an issue when the iterable is a generator where
    // would exhaust it when calling `is_empty`, which results in an
    // exception at the `to_array` call.
    $values = Vec\values($iterable);
    $size = count($values);

    Psl\invariant(0 !== $size, 'Expected a non-empty iterable.');

    /** @var list<Tv> $shuffled */
    $shuffled = Vec\shuffle($values);

    if (1 === $size) {
        /** @var Tv */
        return $shuffled[0];
    }

    return $shuffled[PseudoRandom\int(0, $size - 1)];
}
