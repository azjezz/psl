<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array in which each value appears exactly once. In case of
 * duplicate values, later keys will overwrite the previous ones.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function unique(array $iterable): array
{
    /** @psalm-var array<Tk, Tv> */
    return Iter\to_array_with_keys(
        /** @psalm-var iterable<Tk, Tv> */
        Iter\flip(
            /** @psalm-var iterable<Tv, Tk> */
            Iter\flip($iterable)
        )
    );
}
