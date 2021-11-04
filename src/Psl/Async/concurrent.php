<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Dict;

/**
 * Create a new fiber asynchronously for each one of the given callables, and wait for it to complete.
 *
 * If one or more callables fail, all callables will be completed before throwing.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, (callable(): Tv)> $callables
 *
 * @return array<Tk, Tv> unwrapped values with the order preserved.
 */
function concurrent(iterable $callables): array
{
    $awaitables = Dict\map(
        $callables,
        /**
         * @param callable(): Tv $callable
         *
         * @return Awaitable<Tv>
         */
        static fn(callable $callable): Awaitable => run($callable),
    );

    return namespace\all($awaitables);
}
