<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl\Iter;
use Psl\Math;

/**
 * Returns a list where each element is a pair that combines, pairwise,
 * the elements of the two given iterables.
 *
 * If the iterables are not of equal length, the result will have
 * the same number of elements as the shortest iterable.
 *
 * Elements of the longer iterable after the length of the shorter one
 * will be ignored.
 *
 *  Examples:
 *
 *     Vec\zip([1, 2, 3], [4, 5, 6])
 *     => Vec(
 *         Arr(1, 4)
 *         Arr(2, 5)
 *         Arr(3, 6)
 *     )
 *
 * @template Tv
 * @template Tu
 *
 * @param iterable<Tv> $first
 * @param iterable<Tu> $second
 *
 * @return list<array{0: Tv, 1: Tu}>
 */
function zip(iterable $first, iterable $second): array
{
    $one = namespace\values($first);

    $two = namespace\values($second);

    $result = [];
    $lesser_count = Math\minva(Iter\count($one), Iter\count($two));
    for ($i = 0; $i < $lesser_count; ++$i) {
        $result[] = [$one[$i], $two[$i]];
    }

    return $result;
}
