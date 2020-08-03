<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns an iterator where:
 *  - values are the result of calling `$value_func` on the original value/key
 *  - keys are the result of calling `$key_func` on the original value/key.
 *
 * Example:
 *
 *      Iter\pull(
 *          Iter\range(0, 10),
 *          fn($k, $v) => Str\chr($v + 65),
 *          fn($k, $v) => $k + (2**$v),
 *      )
 *      => Iter(
 *          0 => 'A', 3 => 'C', 6 => 'D', 11 => 'E', 20 => 'F', 37 => 'G',
 *          70 => 'H', 131 => 'I', 264 => 'J', 521 => 'K', 1034 => 'L'
 *      )
 *
 * @psalm-template  Tk1
 * @psalm-template  Tv1
 * @psalm-template  Tk2
 * @psalm-template  Tv2
 *
 * @psalm-param     iterable<Tk1, Tv1>          $iterable
 * @psalm-param     (callable(Tk1, Tv1): Tv2)   $value_func
 * @psalm-param     (callable(Tk1, Tv1): Tk2)   $key_func
 *
 * @psalm-return    Iterator<Tk2, Tv2>
 *
 * @see             Gen\pull_with_key()
 */
function pull_with_key(iterable $iterable, callable $value_func, callable $key_func): Iterator
{
    return new Iterator(Gen\pull_with_key($iterable, $value_func, $key_func));
}
