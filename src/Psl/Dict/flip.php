<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;
use Psl\Type;

/**
 * Flips the keys and values of an iterable.
 *
 * In case of duplicate values, later keys will overwrite the previous ones.
 *
 * Examples:
 *
 *      Dict\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Dict(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @template Tk of array-key
 * @template Tv of array-key
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tv, Tk>
 */
function flip(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        Psl\invariant(
            Type\array_key()->matches($value),
            'Expected all values to be of type array-key, value of type (%s) provided.',
            gettype($value)
        );

        /** @var Tv $value */
        $result[$value] = $key;
    }

    return $result;
}
