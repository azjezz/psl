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
 * @api
 */
function rewindable<Tk, Tv>(Generator $generator): Iterator<Tk, Tv>
{
    return new Iterator::<Tk, Tv>($generator);
}
