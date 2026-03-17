<?php

declare(strict_types=1);

namespace Psl\Crypto\Internal;

use Closure;
use Psl\Crypto\Exception;
use SodiumException;

/**
 * @template T
 *
 * @param (Closure(): T) $callback
 *
 * @return T
 *
 * @throws Exception\RuntimeException If the sodium operation fails.
 *
 * @internal
 *
 * @codeCoverageIgnore -We can't reproduce this easily, just ignore it.
 */
function call_sodium(Closure $callback): mixed
{
    try {
        return $callback();
    } catch (SodiumException $e) {
        throw new Exception\RuntimeException($e->getMessage(), previous: $e);
    }
}
