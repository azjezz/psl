<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Arr;

/**
 * Returns true if the given iterable contains the key.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable,
 * @psalm-param Tk               $key
 *
 * @psalm-return bool
 *
 * @psalm-pure
 */
function contains_key(iterable $iterable, $key): bool
{
    return Arr\contains_key(to_array_with_keys($iterable), $key);
}
