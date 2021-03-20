<?php

declare(strict_types=1);

final class Fiber
{
    public function __construct(callable $c)
    {
    }

    public static function this(): ?self
    {
        return null;
    }

    public function start()
    {
    }

    public function resume(...$args)
    {
    }

    public function isStarted(): bool
    {
        return false;
    }

    public function isSuspended(): bool
    {
        return false;
    }

    public function isTerminated(): bool
    {
        return false;
    }

    public function throw(\Throwable $exception): void
    {
        throw $exception;
    }

    public static function suspend(callable $callback = null)
    {
    }
}
