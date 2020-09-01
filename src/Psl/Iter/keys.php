<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Internal;

/**
 * Returns the keys of an iterable.
 *
 * Examples:
 *
 *      Iter\keys(['a' => 0, 'b' => 1, 'c' => 2])
 *      => Iter('a', 'b', 'c')
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable Iterable to get keys from
 *
 * @psalm-return Iterator<int, Tk>
 */
function keys(iterable $iterable): Iterator
{
    return Internal\lazy_iterator(static function () use ($iterable): Generator {
        foreach ($iterable as $key => $_) {
            yield $key;
        }
    });
}
