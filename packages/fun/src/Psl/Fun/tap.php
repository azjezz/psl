<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that calls the callback with the value passed to it and returns the value.
 *
 * @param (Closure(T): void) $callback
 *
 * @return (Closure(T): T)
 *
 * @pure
 *
 * @api
 */
function tap<T = mixed>(Closure $callback): Closure
{
    return static function (mixed $value) use ($callback): mixed {
        $callback($value);

        return $value;
    };
}
