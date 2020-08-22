<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Converts an iterable of key and value pairs, into a generator of entries.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 *
 * @psalm-return Generator<int, array{0: Tk, 1: Tv}, mixed, void>
 */
function enumerate(iterable $iterable): Generator
{
    foreach ($iterable as $k => $v) {
        yield [$k, $v];
    }
}
