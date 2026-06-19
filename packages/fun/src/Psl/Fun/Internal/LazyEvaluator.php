<?php

declare(strict_types=1);

namespace Psl\Fun\Internal;

use Closure;

/**
 * @internal
 */
final class LazyEvaluator<T = mixed>
{
    /**
     * @var T|null
     */
    private mixed $instance = null;

    private bool $initialized = false;

    /**
     * @param (Closure(): T) $initializer
     */
    public function __construct(
        private readonly Closure $initializer,
    ) {}

    /**
     * @return T
     */
    public function __invoke(): mixed
    {
        if (!$this->initialized) {
            $this->instance = ($this->initializer)();
            $this->initialized = true;
        }

        return $this->instance;
    }
}
