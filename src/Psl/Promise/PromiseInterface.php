<?php

declare(strict_types=1);

namespace Psl\Promise;

use Closure;
use Throwable;

/**
 * @template T
 */
interface PromiseInterface
{
    /**
     * Transforms a promise's value by applying a function to the promise's fulfillment
     * or rejection value.
     *
     * It is a shortcut for:
     *
     * ```php
     * $promise->then($success, $failure);
     * // same as:
     * $promise->map($success)->catch($failure);
     * ```
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(Throwable): Ts) $failure
     *
     * @return PromiseInterface<Ts>
     */
    public function then(Closure $success, Closure $failure): PromiseInterface;

    /**
     * Attaches a callback that is invoked if this promise is fulfilled.
     *
     * The returned promise is resolved with the return value of the callback,
     * or is rejected with a throwable thrown from the callback.
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     *
     * @return PromiseInterface<Ts>
     */
    public function map(Closure $success): PromiseInterface;

    /**
     * Attaches a callback that is invoked if this promise is rejected.
     *
     * The returned promise is resolved with the return value of the callback,
     * or is rejected with a throwable thrown from the callback.
     *
     * @template Ts
     *
     * @param (Closure(Throwable): Ts) $failure
     *
     * @return PromiseInterface<T|Ts>
     */
    public function catch(Closure $failure): PromiseInterface;

    /**
     * Attaches a callback that is always invoked when the promise is resolved.
     *
     * The returned promise resolves with the same value as this promise once the callback has finished execution.
     *
     * If the callback throws, the returned promise will be rejected with the thrown throwable.
     *
     * @param (Closure(): void) $always
     *
     * @return PromiseInterface<T>
     */
    public function always(Closure $always): PromiseInterface;
}
