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
    $list = Vec\map(
        $functions,
        /**
         * @param (callable(): T) $callable
         */
        static fn (callable $callable): Awaitable => async($callable)
    );

    return all($list);
}
