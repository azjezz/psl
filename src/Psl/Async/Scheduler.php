<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl;
use Revolt\EventLoop;
use Revolt\EventLoop\InvalidCallbackError;
use Revolt\EventLoop\Suspension;

/**
 * Psl wrapper around Revolt scheduler.
 *
 * @see EventLoop
 *
 * @codeCoverageIgnore
 */
final class Scheduler
{
    private function __construct()
    {
    }

    /**
     * Create an object used to suspend and resume execution, either within a fiber or from {main}.
     *
     * @see EventLoop::createSuspension()
     */
    public static function createSuspension(): Suspension
    {
        return EventLoop::createSuspension();
    }

    /**
     * Queue a microtask.
     *
     * @see EventLoop::queue()
     */
    public static function queue(callable $callback): void
    {
        EventLoop::queue($callback);
    }

    /**
     * Defer the execution of a callback.
     *
     * @param (callable(string): void)  $callback   The callback to defer.
     *                                              The `$callbackId` will be invalidated before the callback invocation.
     *
     * @return string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::defer()
     */
    public static function defer(callable $callback): string
    {
        return EventLoop::defer($callback);
    }

    /**
     * Delay the execution of a callback.
     *
     * @param float $delay The amount of time, to delay the execution for in seconds.
     * @param (callable(string): void)  $callback   The callback to delay.
     *                                              The `$callbackId` will be invalidated before the callback invocation.
     *
     * @return string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::delay()
     */
    public static function delay(float $delay, callable $callback): string
    {
        return EventLoop::delay($delay, $callback);
    }

    /**
     * Repeatedly execute a callback.
     *
     * @param int $interval The time interval, to wait between executions in seconds.
     * @param callable(string) $callback The callback to repeat.
     *
     * @return string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::repeat()
     */
    public static function repeat(float $interval, callable $callback): string
    {
        return EventLoop::repeat($interval, $callback);
    }

    /**
     * Enable a callback to be active starting in the next tick.
     *
     * @throws Psl\Exception\InvariantViolationException If the callback identifier is invalid.
     *
     * @see EventLoop::repeat()
     */
    public static function enable(string $callbackId): void
    {
        try {
            EventLoop::enable($callbackId);
        } catch (InvalidCallbackError $error) {
            Psl\invariant_violation($error->getMessage());
        }
    }

    /**
     * Disable a callback immediately.
     *
     * @see EventLoop::disable()
     */
    public static function disable(string $callbackId): void
    {
        EventLoop::disable($callbackId);
    }

    /**
     * Cancel a callback.
     *
     * This will detach the event loop from all resources that are associated to the callback. After this operation the
     * callback is permanently invalid. Calling this function MUST NOT fail, even if passed an invalid identifier.
     *
     * @param string $callbackId The callback identifier.
     *
     * @see EventLoop::cancel()
     */
    public static function cancel(string $callbackId): void
    {
        EventLoop::cancel($callbackId);
    }

    /**
     * Reference a callback.
     *
     * This will keep the event loop alive whilst the event is still being monitored. Callbacks have this state by
     * default.
     *
     * @throws Psl\Exception\InvariantViolationException If the callback identifier is invalid.
     *
     * @see EventLoop::reference()
     */
    public static function reference(string $callbackId): void
    {
        try {
            EventLoop::reference($callbackId);
        } catch (InvalidCallbackError $error) {
            Psl\invariant_violation($error->getMessage());
        }
    }

    /**
     * Unreference a callback.
     *
     * The event loop should exit the run method when only unreferenced callbacks are still being monitored. Callbacks
     * are all referenced by default.
     *
     * @see EventLoop::unreference()
     */
    public static function unreference(string $callbackId): void
    {
        EventLoop::unreference($callbackId);
    }
}
