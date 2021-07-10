<?php

declare(strict_types=1);

namespace Psl\Fun\Internal;

/**
 * @internal
 * @template T
 */
final class LazyEvaluator
{
    /**
     * @var T|null
     */
    private mixed $instance = null;

    /**
     * @var callable(): T
     */
    private $initializer;

    private bool $initialized = false;

    /**
     * @param callable(): T $initializer
     */
    public function __construct(callable $initializer)
    {
        $this->initializer = $initializer;
    }

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
