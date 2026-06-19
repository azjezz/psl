<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Create a rewindable iterator from the given generator without
 * exhausting the generator immediately.
 *
 * @param Generator<Tk, Tv, mixed, mixed> $generator
 *
 * @return Iterator<Tk, Tv>
 *
 * @api
 */
function rewindable<Tk = mixed, Tv = mixed>(Generator $generator): Iterator<Tk, Tv>
{
    return new Iterator($generator);
}
