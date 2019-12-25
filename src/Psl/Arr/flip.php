<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Str;

/**
 * Flips the keys and values of an iterable. In case of
 * duplicate values, later keys will overwrite the previous ones.
 *
 * Examples:
 *
 *      Iter\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Iter(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv as array-key
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return array<Tv, Tk>
 */
function flip(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $k => $v) {
        Psl\invariant(
            Str\is_string($v) || is_numeric($v),
            'Expected all values to be of type array-key, value of type (%s) provided.',
            gettype($v)
        );

        $result[$v] = $k;
    }

    /** @psalm-var array<Tv, Tk> $result*/
    return $result;
}
