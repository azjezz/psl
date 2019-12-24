<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Converts an iterable of key and value pairs, into an iterable of entries.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 *
 * @psalm-return iterable<array{0: Tk, 1: Tv}>
 */
function enumerate(iterable $iterable): iterable
{
    foreach ($iterable as $k => $v) {
        yield [$k, $v];
    }
}
