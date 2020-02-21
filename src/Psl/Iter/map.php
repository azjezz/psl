<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Applies a mapping function to all values of an iterator.
 *
 * The function is passed the current iterator value and should return a
 * modified iterator value. The key is left as-is and not passed to the mapping
 * function.
 *
 * Examples:
 *
 *     Iter\map([1, 2, 3, 4, 5], fn($i) => $i * 2);
 *     => Iter(2, 4, 6, 8, 10)
 *
 * @psalm-template  Tk of array-key
 * @psalm-template  Tv
 * @psalm-template  Tu
 *
 * @psalm-param     iterable<Tk, Tv>    $iterable Iterable to be mapped over
 * @psalm-param     (callable(Tv): Tu)  $function
 *
 * @psalm-return    Iterator<Tk, Tu>
 *
 * @see             Gen\map()
 */
function map(iterable $iterable, callable $function): Iterator
{
    return new Iterator(Gen\map($iterable, $function));
}
