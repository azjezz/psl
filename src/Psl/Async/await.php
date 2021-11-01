<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Awaits the given awaitable.
 *
 * @template T
 *
 * @param Awaitable<T> $awaitable
 *
 * @return T
 *
 * @see Awaitable::await()
 */
function await(Awaitable $awaitable): mixed
{
    return $awaitable->await();
}
