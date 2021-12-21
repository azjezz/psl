<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that can be used for lazy evaluation.
 *
 * @template T
 *
 * @param (Closure(): T) $initializer
 *
 * @return (Closure(): T)
 */
function lazy(Closure $initializer): Closure
{
    $evaluator = new Internal\LazyEvaluator($initializer);

    return static fn() => $evaluator();
}
