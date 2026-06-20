<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl;
use Throwable;

/**
 * The following class was derived from code of Amphp.
 *
 * https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/lib/Deferred.php
 *
 * Code subject to the MIT license (https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/LICENSE).
 *
 * Copyright (c) 2015-2021 Amphp ( https://amphp.org )
 *
 * @api
 */
final readonly class Deferred<T>
{
    private Internal\State<T> $state;

    private Awaitable<T> $awaitable;

    public function __construct()
    {
        $this->state = new Internal\State::<T>();
        $this->awaitable = new Awaitable::<T>($this->state);
    }

    /**
     * Completes the operation with a result value.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     */
    public function complete(T $result): void
    {
        $this->state->complete($result);
    }

    /**
     * Marks the operation as failed.
     *
     * @param Throwable $throwable Throwable to indicate the error.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     */
    public function error(Throwable $throwable): void
    {
        $this->state->error($throwable);
    }

    /**
     * @return bool True if the operation has completed.
     *
     * @psalm-mutation-free
     */
    public function isComplete(): bool
    {
        return $this->state->isComplete();
    }

    /**
     * @return Awaitable<T> The awaitable associated with this Deferred.
     *
     * @psalm-mutation-free
     */
    public function getAwaitable(): Awaitable<T>
    {
        return $this->awaitable;
    }
}
