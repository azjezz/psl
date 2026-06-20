<?php

declare(strict_types=1);

namespace Psl\Fun\Internal;

use Closure;

/**
 * @internal
 */
final class LazyEvaluator<T>
{
    private T|null $instance = null;

    private bool $initialized = false;

    /**
     * @param (Closure(): T) $initializer
     */
    public function __construct(
        private readonly Closure $initializer,
    ) {}

    public function __invoke(): T
    {
        if (!$this->initialized) {
            $this->instance = ($this->initializer)();
            $this->initialized = true;
        }

        return $this->instance;
    }
}
