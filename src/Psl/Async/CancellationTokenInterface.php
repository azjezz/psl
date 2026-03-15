<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

/**
 * A token that can be used to cancel asynchronous operations.
 *
 * Implementations allow operations to be cancelled either manually
 * ({@see SignalCancellationToken}) or automatically after a timeout
 * ({@see TimeoutCancellationToken}).
 */
interface CancellationTokenInterface
{
    /**
     * Whether this token can ever be cancelled.
     *
     * When false, operations can skip subscribe/unsubscribe overhead entirely.
     */
    public bool $cancellable { get; }

    /**
     * Register a callback to be invoked when the token is cancelled.
     *
     * If the token is already cancelled, the callback is invoked immediately.
     *
     * @param (Closure(Exception\CancelledException): void) $callback
     *
     * @return non-empty-string Subscription identifier for use with {@see unsubscribe()}.
     */
    public function subscribe(Closure $callback): string;

    /**
     * Remove a previously registered callback.
     *
     * @param non-empty-string $id
     */
    public function unsubscribe(string $id): void;

    /**
     * Check whether the token has been cancelled.
     */
    public function isCancelled(): bool;

    /**
     * Throw a {@see Exception\CancelledException} if the token has been cancelled.
     *
     * @throws Exception\CancelledException If the token has been cancelled.
     */
    public function throwIfCancelled(): void;
}
