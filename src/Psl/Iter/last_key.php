<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Arr;

/**
 * Get the last key of an iterable, if the iterable is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    array<Tk, Tv> $iterable
 *
 * @psalm-return   null|Tk
 *
 * @see            Arr\last_key()
 */
function last_key(iterable $iterable)
{
    /** @psalm-var null|Tk */
    return Arr\last_key(to_array_with_keys($iterable));
}
