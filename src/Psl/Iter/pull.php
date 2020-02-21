<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns an iterator where:
 *  - values are the result of calling `$value_func` on the original value
 *  - keys are the result of calling `$key_func` on the original value.
 *
 * Example:
 *
 *      Iter\pull(
 *          Iter\range(0, 10),
 *          fn($i) => Str\chr($i + 65),
 *          fn($i) => 2**$i,
 *      )
 *      => Iter(
 *          0 => 'A', 2 => 'C', 4 => 'D', 8 => 'E', 16 => 'F', 32 => 'G',
 *          64 => 'H', 124 => 'I', 256 => 'J', 512 => 'K', 1024 => 'L'
 *      )
 *
 *
 * @psalm-template  T
 * @psalm-template  Tk of array-key
 * @psalm-template  Tv
 *
 * @psalm-param     iterable<T>         $iterable
 * @psalm-param     (callable(T): Tv)   $value_func
 * @psalm-param     (callable(T): Tk)   $key_func
 *
 * @psalm-return    Iterator<Tk, Tv>
 *
 * @see             Gen\pull()
 */
function pull(iterable $iterable, callable $value_func, callable $key_func): Iterator
{
    return new Iterator(Gen\pull($iterable, $value_func, $key_func));
}
