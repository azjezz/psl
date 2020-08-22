<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns an iterator where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @psalm-template  Tk
 * @psalm-template  Tv
 *
 * @psalm-param     iterable<Tk>        $keys
 * @psalm-param     (callable(Tk): Tv)  $value_func
 *
 * @psalm-return    Iterator<Tk, Tv>
 *
 * @see             Gen\from_keys()
 */
function from_keys(iterable $keys, callable $value_func): Iterator
{
    return new Iterator(Gen\from_keys($keys, $value_func));
}
