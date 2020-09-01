<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl\Iter;

/**
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param (callable(): \Generator<Tk, Tv, mixed, mixed>) $fun
 *
 * @psalm-return Iter\Iterator<Tk, Tv>
 */
function lazy_iterator(callable $fun): Iter\Iterator
{
    return new Iter\Iterator($fun());
}
