<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * This method creates a callback that returns the value passed as argument.
 * It can e.g. be used as a success callback.
 *
 * @template T
 *
 * @return (Closure(T): T)
 *
 * @pure
 */
function identity(): Closure
{
    return static fn ($result) => $result;
}
