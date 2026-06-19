<?php

declare(strict_types=1);

namespace Psl\Promise;

use Closure;
use Throwable;

/**
 * @api
 */
interface PromiseInterface<T = mixed>
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
     * @param (Closure(T): Ts) $success
     * @param (Closure(Throwable): Ts) $failure
     *
     * @return PromiseInterface<Ts>
     */
    public function then<Ts = mixed>(Closure $success, Closure $failure): PromiseInterface<Ts>;

    /**
     * Attaches a callback that is invoked if this promise is fulfilled.
     *
     * The returned promise is resolved with the return value of the callback,
     * or is rejected with a throwable thrown from the callback.
     *
     * @param (Closure(T): Ts) $success
     *
     * @return PromiseInterface<Ts>
     */
    public function map<Ts = mixed>(Closure $success): PromiseInterface<Ts>;

    /**
     * Attaches a callback that is invoked if this promise is rejected.
     *
     * The returned promise is resolved with the return value of the callback,
     * or is rejected with a throwable thrown from the callback.
     *
     * @param (Closure(Throwable): Ts) $failure
     *
     * @return PromiseInterface<T|Ts>
     */
    public function catch<Ts = mixed>(Closure $failure): PromiseInterface<T|Ts>;

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
