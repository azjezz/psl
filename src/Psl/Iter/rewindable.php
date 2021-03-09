<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Create a rewindable iterator from the given generator without
 * exhausting the generator immediately.
 *
 * @template Tk
 * @template Tv
 *
 * @param Generator<Tk, Tv, mixed, mixed> $generator
 *
 * @return Iterator<Tk, Tv>
 */
function rewindable(Generator $generator): Iterator
{
    return new Iterator($generator);
}
