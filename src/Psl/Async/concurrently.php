<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Dict;

/**
 * Create a new fiber asynchronously using the given callables.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, (callable(): Tv)> $callables
 *
 * @return Awaitable<array<Tk, Tv>> unwrapped values with the order preserved.
 */
function concurrently(iterable $callables): Awaitable
{
    return run(static function () use ($callables): array {
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
    });
}
