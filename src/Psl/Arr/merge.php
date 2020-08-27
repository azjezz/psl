<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Merges multiple arrays into a new array. In the case of duplicate
 * keys, later values will overwrite the previous ones.
 *
 * Example:
 *      Arr\merge([1, 2], [9, 8])
 *      => Arr(0 => 9, 1 => 8)
 *
 *      Arr\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Arr(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    array<Tk, Tv>        $first
 * @psalm-param    list<array<Tk, Tv>>  $rest
 *
 * @psalm-return   array<Tk, Tv>
 *
 * @psalm-pure
 */
function merge(array $first, array ...$rest): array
{
    /** @psalm-var list<array<Tk, Tv>> $arrays */
    $arrays = [$first, ...$rest];

    return flatten($arrays);
}
