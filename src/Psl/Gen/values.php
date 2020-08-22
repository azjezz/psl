<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Yield the values of an iterable, making the keys continuously indexed.
 *
 * Examples:
 *
 *      Gen\values(['a' => 'foo', 'b' => 'bar', 'c' => 'baz'])
 *      => Gen('foo', 'bar', 'baz')
 *
 *      Gen\values([17 => 1, 42 => 2, -2 => 100])
 *      => Gen(1, 42, 100)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable Iterable to get values from
 *
 * @psalm-return Generator<int, Tv, mixed, void>
 */
function values(iterable $iterable): Generator
{
    foreach ($iterable as $value) {
        yield $value;
    }
}
