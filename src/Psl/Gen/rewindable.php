<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Create a rewindable iterator from the given generator without
 * exhausting the generator immediately.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    Generator<Tk, Tv, mixed, mixed> $generator
 *
 * @psalm-return   RewindableGenerator<Tk, Tv>
 */
function rewindable(Generator $generator): RewindableGenerator
{
    return new RewindableGenerator($generator);
}
