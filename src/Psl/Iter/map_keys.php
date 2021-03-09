<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Applies a mapping function to all keys of an iterator.
 *
 * The function is passed the current iterator key and should return a
 * modified iterator key.
 *
 * The value is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Iter\map_keys([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5], fn($i) => $i * 2);
 *     => Iter(0 => 1, 2 => 2, 4 => 3, 6 => 4, 8 => 5)
 *
 * @template Tk1
 * @template Tk2
 * @template Tv
 *
 * @param iterable<Tk1, Tv> $iterable Iterable to be mapped over
 * @param (callable(Tk1): Tk2) $function
 *
 * @return Iterator<Tk2, Tv>
 *
 * @deprecated use `Dict\map_keys` instead.
 * @see Dict\map_keys()
 */
function map_keys(iterable $iterable, callable $function): Iterator
{
    return Iterator::from(static function () use ($iterable, $function): Generator {
        foreach ($iterable as $key => $value) {
            yield $function($key) => $value;
        }
    });
}
