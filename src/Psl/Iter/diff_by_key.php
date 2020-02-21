<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>       $first
 * @psalm-param    iterable<Tk, mixed>    $second
 * @psalm-param    iterable<Tk, mixed>    ...$rest
 *
 * @psalm-return   Iterator<Tk, iterable<Tk, Tv>>
 *
 * @see            Gen\diff_by_key()
 */
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): Iterator
{
    return new Iterator(Gen\diff_by_key($first, $second, ...$rest));
}
