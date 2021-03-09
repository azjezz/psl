<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl;

/**
 * Shuffle the given iterable items.
 *
 * Example:
 *
 *      Vec\shuffle([1, 2, 3])
 *      => Vec(2, 3, 1)
 *
 *      Vec\shuffle(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Vec(2, 3, 1)
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return list<T> the shuffled items as a list.
 */
function shuffle(iterable $iterable): array
{
    $array = namespace\values($iterable);

    $shuffled = \shuffle($array);
    /** @psalm-suppress MissingThrowsDocblock */
    Psl\invariant($shuffled, 'unexpected error: unable to shuffle array.');

    return $array;
}
