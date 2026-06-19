<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Take the first n elements from an iterable.
 *
 * Examples:
 *
 *      Dict\take(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2)
 *      => Dict('a' => 1, 'b' => 2)
 *
 * @param iterable<Tk, Tv> $iterable Iterable to take the elements from
 * @param int<0, max> $n Number of elements to take from the start
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function take<Tk : int|string = int|string, Tv = mixed>(iterable $iterable, int $n): array
{
    return namespace\slice($iterable, 0, $n);
}
