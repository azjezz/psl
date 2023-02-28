<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Try running an action $try.
 * In case of success, it will return the result of the $try Closure.
 * In case of failure, it will try to recover with the $catch Closure.
 * The $catch Closure may still throw exceptions of which it will not recover.
 *
 * @template T
 * @template Ts
 *
 * @param (Closure(): T) $try
 * @param (Closure(Throwable): Ts) $catch
 *
 * @return T|Ts
 */
function try_catch(Closure $try, Closure $catch): mixed
{
    return namespace\wrap($try)->catch($catch)->getResult();
}
