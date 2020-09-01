<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Arr;

/**
 * Retrieve a random value from a non-empty iterable.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Tv
 *
 * @throws Psl\Exception\InvariantViolationException If $iterable is empty.
 */
function random(iterable $iterable)
{
    // We convert the iterable to an array before checking if it is empty,
    // this helps us avoids an issue when the iterable is a generator where
    // would exhaust it when calling `is_empty`, which results in an
    // exception at the `to_array` call.
    $array = to_array($iterable);

    Psl\invariant(!is_empty($array), 'Expected a non-empty iterable.');

    /** @psalm-var Tv */
    return Arr\random($array);
}
