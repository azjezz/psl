<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Awaits the given awaitable.
 *
 * @param Awaitable<T> $awaitable
 *
 * @return T
 *
 * @see Awaitable::await()
 *
 * @api
 */
function await<T>(Awaitable<T> $awaitable): T
{
    return $awaitable->await();
}
