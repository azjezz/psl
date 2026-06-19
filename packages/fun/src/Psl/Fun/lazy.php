<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that can be used for lazy evaluation.
 *
 * @param (Closure(): T) $initializer
 *
 * @return (Closure(): T)
 *
 * @api
 */
function lazy<T = mixed>(Closure $initializer): Closure
{
    $evaluator = new Internal\LazyEvaluator($initializer);

    return $evaluator(...);
}
