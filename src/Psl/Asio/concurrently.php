<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl\Vec;

/**
 * Run multiple functions concurrently.
 *
 * @template T
 *
 * @param iterable<(callable(): T)> $functions
 *
 * @return Awaitable<list<T>>
 */
function concurrent(iterable $functions): Awaitable
{
    return all(Vec\map(
        $functions,
        /**
         * @param (callable(): T) $callable
         *
         * @return Awaitable<T>
         */
        static fn (callable $callable): Awaitable => async($callable)
    ));
}
