<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Returns the keys of an iterable.
 *
 * Examples:
 *
 *      Iter\keys(['a' => 0, 'b' => 1, 'c' => 2])
 *      => Iter('a', 'b', 'c')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to get keys from
 *
 * @return Iterator<int, Tk>
 *
 * @deprecated since 1.2, use Vec\keys instead.
 * @see Vec\keys()
 */
function keys(iterable $iterable): Iterator
{
    return Iterator::from(static function () use ($iterable): Generator {
        foreach ($iterable as $key => $_) {
            yield $key;
        }
    });
}
