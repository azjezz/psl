<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;
use Exception;

/**
 * Returns a closure that rethrows the exception passed to it.
 *
 * @return (Closure(Exception): never)
 *
 * @pure
 */
function rethrow(): Closure
{
    return static function (Exception $exception): never {
        throw $exception;
    };
}
