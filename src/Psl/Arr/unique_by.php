<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;
use Psl\Str;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 * @psalm-template Ts as array-key
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 * @psalm-param (callable(Tv): Ts)  $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 * @return array
 */
function unique_by(iterable $iterable, callable $scalar_func): array
{
    $iterable = Iter\to_array_with_keys($iterable);
    $unique = [];
    foreach ($iterable as $k => $v) {
        $scalar = $scalar_func($v);
        Psl\invariant(
            Str\is_string($scalar) || is_numeric($scalar),
            'Expected return value of $scalar_func to be of type array-key, value of type (%s) returned.',
            gettype($scalar)
        );

        if (!contains($unique, $scalar)) {
            $unique[$k] = $scalar;
        }
    }

    return Iter\to_array_with_keys(
        Iter\map_with_key(
            $unique,
            fn ($k, $v) => $iterable[$k]
        )
    );
}
