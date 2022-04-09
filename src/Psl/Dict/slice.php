<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Dict\slice(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2)
 *      => Dict('c' => 3, 'd' => 4)
 *
 *      Dict\slice(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 1, 2)
 *      => Dict('b' => 2, 'c' => 3)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to take the slice from
 * @param int<0, max> $start Start offset
 * @param null|int<0, max> $length Length (if not specified all remaining values from the array are used)
 *
 * @return array<Tk, Tv>
 */
function slice(iterable $iterable, int $start, ?int $length = null): array
{
    $result = [];
    if (0 === $length) {
        return $result;
    }

    $i = 0;
    foreach ($iterable as $key => $value) {
        if ($i++ < $start) {
            continue;
        }

        $result[$key] = $value;
        if (null !== $length && $i >= $start + $length) {
            break;
        }
    }

    return $result;
}
