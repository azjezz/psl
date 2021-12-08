<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl;
use Revolt\EventLoop;
use Revolt\EventLoop\Driver;
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
     * Execute a callback when a signal is received.
     *
     * @param int $signal_number The signal number to monitor.
     * @param (Closure(string, int): void) $callback The callback to execute.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::onSignal()
     */
    public static function onSignal(int $signal_number, Closure $callback): string
    {
        /**
         * @psalm-suppress MissingThrowsDocblock
         *
         * @var non-empty-string
         */
        return EventLoop::onSignal($signal_number, $callback);
    }

    /**
     * Execute a callback when a stream resource becomes readable or is closed for reading.
     *
     * @param resource|object $stream The stream to monitor.
     * @param (Closure(string, resource|object): void) $callback   The callback to execute.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     */
    public static function onReadable(mixed $stream, Closure $callback): string
    {
        /** @var non-empty-string */
        return EventLoop::onReadable($stream, $callback);
    }

    /**
     * Execute a callback when a stream resource becomes writable or is closed for writing.
     *
     * @param resource|object $stream The stream to monitor.
     * @param (Closure(string, resource|object): void) $callback   The callback to execute.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     */
    public static function onWritable(mixed $stream, Closure $callback): string
    {
        /** @var non-empty-string */
        return EventLoop::onWritable($stream, $callback);
    }

    /**
     * Queue a microtask.
     *
     * @see EventLoop::queue()
     */
    public static function queue(Closure $callback): void
    {
        EventLoop::queue($callback);
    }

    /**
     * Defer the execution of a callback.
     *
     * @param (Closure(string): void)  $callback   The callback to defer.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::defer()
     */
    public static function defer(Closure $callback): string
    {
        /** @var non-empty-string */
        return EventLoop::defer($callback);
    }

    /**
     * Delay the execution of a callback.
     *
     * @param float $delay The amount of time, to delay the execution for in seconds.
     * @param (Closure(string): void)   $callback   The callback to delay.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::delay()
     */
    public static function delay(float $delay, Closure $callback): string
    {
        /** @var non-empty-string */
        return EventLoop::delay($delay, $callback);
    }

    /**
     * Repeatedly execute a callback.
     *
     * @param float $interval The time interval, to wait between executions in seconds.
     * @param (Closure(string): void) $callback The callback to repeat.
     *
     * @return non-empty-string A unique identifier that can be used to cancel, enable or disable the callback.
     *
     * @see EventLoop::repeat()
     */
    public static function repeat(float $interval, Closure $callback): string
    {
        /** @var non-empty-string */
        return EventLoop::repeat($interval, $callback);
    }

    /**
     * Enable a callback to be active starting in the next tick.
     *
     * @param non-empty-string $identifier The callback identifier.
     *
     * @throws Psl\Exception\InvariantViolationException If the callback identifier is invalid.
     *
     * @see EventLoop::repeat()
     */
    public static function enable(string $identifier): void
    {
        try {
            EventLoop::enable($identifier);
        } catch (InvalidCallbackError $error) {
            Psl\invariant_violation($error->getMessage());
        }
    }

    /**
     * Disable a callback immediately.
     *
     * @param string $identifier The callback identifier.
     *
     * @see EventLoop::disable()
     */
    public static function disable(string $identifier): void
    {
        EventLoop::disable($identifier);
    }

    /**
     * Cancel a callback.
     *
     * @param string $identifier The callback identifier.
     *
     * @see EventLoop::cancel()
     */
    public static function cancel(string $identifier): void
    {
        EventLoop::cancel($identifier);
    }

    /**
     * Reference a callback.
     *
     * @param non-empty-string $identifier The callback identifier.
     *
     * @throws Psl\Exception\InvariantViolationException If the callback identifier is invalid.
     *
     * @see EventLoop::reference()
     */
    public static function reference(string $identifier): void
    {
        try {
            EventLoop::reference($identifier);
        } catch (InvalidCallbackError $error) {
            Psl\invariant_violation($error->getMessage());
        }
    }

    /**
     * Unreference a callback.
     *
     * @param string $identifier The callback identifier.
     *
     * @see EventLoop::unreference()
     */
    public static function unreference(string $identifier): void
    {
        EventLoop::unreference($identifier);
    }

    /**
     * Run the event loop.
     *
     * @see Driver::run()
     */
    public static function run(): void
    {
        EventLoop::getDriver()->run();
    }
}
