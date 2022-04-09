<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Vec\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => Vec(0, 1, 2, 3, 4, 5)
 *
 *      Vec\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Vec(0, 1, 2)
 *
 * @template T
 *
 * @param iterable<T> $iterable Iterable to take the slice from
 * @param int<0, max> $start Start offset
 * @param null|int<0, max> $length Length (if not specified all remaining values from the array are used)
 *
 * @return list<T>
 */
function slice(iterable $iterable, int $start, ?int $length = null): array
{
    $result = [];
    if (0 === $length) {
        return $result;
    }

    $i = 0;
    foreach ($iterable as $value) {
        if ($i++ < $start) {
            continue;
        }

        $result[] = $value;
        if (null !== $length && $i >= $start + $length) {
            break;
        }
    }

    return $result;
}
