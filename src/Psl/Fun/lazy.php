<?php

declare(strict_types=1);

namespace Psl\Fun;

use Psl\Fun\Internal\LazyEvaluator;

/**
 * @template T
 *
 * @param callable(): T $initializer
 *
 * @return callable(): T
 */
function lazy(callable $initializer): callable
{
    $evaluator = new LazyEvaluator($initializer);

    return static fn() => $evaluator();
}
