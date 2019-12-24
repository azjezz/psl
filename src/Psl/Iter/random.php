<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Arr;

/**
 * Retrieve a random value from a non-empty iterable.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Tv
 */
function random(iterable $iterable)
{
    Psl\invariant(!is_empty($iterable), 'Expected a non-empty iterable.');

    /** @psalm-var Tv */
    return Arr\random(to_array($iterable));
}
