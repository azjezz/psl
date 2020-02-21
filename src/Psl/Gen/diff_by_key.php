<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

use Psl\Arr;
use Psl\Iter;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>       $first
 * @psalm-param iterable<Tk, mixed>    $second
 * @psalm-param iterable<Tk, mixed>    ...$rest
 *
 * @psalm-return Generator<Tk, iterable<Tk, Tv>, mixed, void>
 */
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): Generator
{
    if (Iter\is_empty($first)) {
        return;
    }

    if (Iter\is_empty($second) && Iter\is_empty($rest)) {
        yield from $first;
    }

    $other = Arr\flatten([$second, ...$rest]);
    /** @psalm-var iterable<Tk, Tv> */
    foreach ($first as $k => $v) {
        if (!Iter\contains_key($other, $k)) {
            yield $k => $v;
        }
    }
}
