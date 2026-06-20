<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Dict\drop(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2)
 *      => Dict('c' => 3, 'd' => 4)
 *
 * @param iterable<Tk, Tv> $iterable Iterable to drop the elements from
 * @param int<0, max> $n Number of elements to drop from the start
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function drop<Tk : int|string, Tv>(iterable $iterable, int $n): array
{
    return namespace\slice($iterable, $n);
}
