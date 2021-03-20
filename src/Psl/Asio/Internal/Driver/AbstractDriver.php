<?php

declare(strict_types=1);

namespace Psl\Asio\Internal\Driver;

use Error;
use Psl;
use Psl\Asio\Internal\Watcher;
use Psl\Type;
use Throwable;

use function array_merge;
use function microtime;

/**
 * Event loop driver which implements all basic operations to allow interoperability.
 *
 * Watchers (enabled or new watchers) MUST immediately be marked as enabled, but only be activated (i.e. callbacks can
 * be called) right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
 *
 * All registered callbacks MUST NOT be called from a file with strict types enabled (`declare(strict_types=1)`).
 *
 * @internal
 */
abstract class AbstractDriver implements DriverInterface
{
    // Don't use 1e3 / 1e6, they result in a float instead of int
    protected const MILLISEC_PER_SEC = 1000;
    protected const MICROSEC_PER_SEC = 1000000;

    /**
     * @var string Next watcher ID.
     */
    private string $nextId = "a";

    /**
     *  @var array<string, Watcher<int>|Watcher<resource>|Watcher<null>>
     */
    private array $watchers = [];

    /**
     * @var array<string, Watcher<int>|Watcher<resource>|Watcher<null>>
     */
    private array $enableQueue = [];

    /**
     * @var array<string, Watcher<int>|Watcher<resource>|Watcher<null>>
     */
    private array $deferQueue = [];

    /**
     * @var array<string, Watcher<int>|Watcher<resource>|Watcher<null>>
     */
    private array $nextTickQueue = [];

    /**
     * @var callable(\Throwable):void|null
     */
    private $errorHandler = null;

    /**
     * @var mixed[]
     */
    private array $registry = [];

    private bool $running = false;

    /**
     * Run the event loop.
     *
     * One iteration of the loop is called one "tick". A tick covers the following steps:
     *
     * 1. Activate watchers created / enabled in the last tick / before `run()`.
     * 2. Execute all enabled defer watchers.
     * 3. Execute all due timer, pending signal and actionable stream callbacks, each only once per tick.
     *
     * The loop MUST continue to run until it is either stopped explicitly, no referenced watchers exist anymore, or an
     * exception is thrown that cannot be handled. Exceptions that cannot be handled are exceptions thrown from an
     * error handler or exceptions that would be passed to an error handler but none exists to handle them.
     *
     * @throws Error Thrown if the event loop is already running.
     */
    public function run(): void
    {
        if ($this->running) {
            throw new Error("The loop was already running");
        }

        $this->running = true;

        try {
            while ($this->running) {
                if ($this->isEmpty()) {
                    return;
                }

                $this->tick();
            }
        } finally {
            $this->stop();
        }
    }

    /**
     * Stop the event loop.
     *
     * When an event loop is stopped, it continues with its current tick and exits the loop afterwards. Multiple calls
     * to stop MUST be ignored and MUST NOT raise an exception.
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * @return bool True if the event loop is running, false if it is stopped.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Defer the execution of a callback.
     *
     * The deferred callable MUST be executed before any other type of watcher in a tick. Order of enabling MUST be
     * preserved when executing the callbacks.
     *
     * The created watcher MUST immediately be marked as enabled, but only be activated (i.e. callback can be called)
     * right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @template T
     *
     * @param callable(string, T) $callback The callback to defer.
     *                                      The `$watcherId` will be invalidated before the callback call.
     * @param T $data Arbitrary data given to the callback function as the `$data` parameter.
     *
     * @return string An unique identifier that can be used to cancel, enable or disable the watcher.
     */
    public function defer(callable $callback, $data = null): string
    {
        /** @var Watcher<null> $watcher */
        $watcher = new Watcher();
        $watcher->type = Watcher::DEFER;
        /** @psalm-suppress StringIncrement */
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->data = $data;

        $this->watchers[$watcher->id] = $watcher;
        $this->nextTickQueue[$watcher->id] = $watcher;

        return $watcher->id;
    }

    /**
     * Delay the execution of a callback.
     *
     * The delay is a minimum and approximate, accuracy is not guaranteed. Order of calls MUST be determined by which
     * timers expire first, but timers with the same expiration time MAY be executed in any order.
     *
     * The created watcher MUST immediately be marked as enabled, but only be activated (i.e. callback can be called)
     * right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @template T
     *
     * @param int $delay The amount of time, in milliseconds, to delay the execution for.
     * @param callable(string, T) $callback The callback to delay.
     *                                      The `$watcherId` will be invalidated before the callback call.
     * @param T $data Arbitrary data given to the callback function as the `$data` parameter.
     *
     * @throws Psl\Exception\InvariantViolationException If $delay is negative.
     *
     * @return string An unique identifier that can be used to cancel, enable or disable the watcher.
     */
    public function delay(int $delay, callable $callback, $data): string
    {
        Psl\invariant($delay >= 0, '$delay must be greater than or equal to zero.');

        /** @var Watcher<int> $watcher */
        $watcher = new Watcher();
        $watcher->type = Watcher::DELAY;
        /** @psalm-suppress StringIncrement */
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $delay;
        $watcher->expiration = $this->now() + $delay;
        $watcher->data = $data;

        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;

        return $watcher->id;
    }

    /**
     * Repeatedly execute a callback.
     *
     * The interval between executions is a minimum and approximate, accuracy is not guaranteed. Order of calls MUST be
     * determined by which timers expire first, but timers with the same expiration time MAY be executed in any order.
     * The first execution is scheduled after the first interval period.
     *
     * The created watcher MUST immediately be marked as enabled, but only be activated (i.e. callback can be called)
     * right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @template T
     *
     * @param int $interval The time interval, in milliseconds, to wait between executions.
     * @param callable(string, T) $callback The callback to delay.
     *                                      The `$watcherId` will be invalidated before the callback call.
     * @param T $data Arbitrary data given to the callback function as the `$data` parameter.
     *
     * @throws Psl\Exception\InvariantViolationException If $delay is negative.
     *
     * @return string An unique identifier that can be used to cancel, enable or disable the watcher.
     */
    public function repeat(int $interval, callable $callback, $data): string
    {
        Psl\invariant($interval >= 0, '$interval must be greater than or equal to zero.');
        /** @var Watcher<int> $watcher */
        $watcher = new Watcher();
        $watcher->type = Watcher::REPEAT;
        /** @psalm-suppress StringIncrement */
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $interval;
        $watcher->expiration = $this->now() + $interval;
        $watcher->data = $data;

        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;

        return $watcher->id;
    }

    /**
     * Execute a callback when a stream resource becomes readable or is closed for reading.
     *
     * Warning: Closing resources locally, e.g. with `fclose`, might not invoke the callback. Be sure to `cancel` the
     * watcher when closing the resource locally. Drivers MAY choose to notify the user if there are watchers on invalid
     * resources, but are not required to, due to the high performance impact. Watchers on closed resources are
     * therefore undefined behavior.
     *
     * Multiple watchers on the same stream MAY be executed in any order.
     *
     * The created watcher MUST immediately be marked as enabled, but only be activated (i.e. callback can be called)
     * right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @template T
     *
     * @param resource $stream The stream to monitor.
     * @param callable(string, resource, T) $callback The callback to execute.
     * @param T $data Arbitrary data given to the callback function as the `$data` parameter.
     *
     * @return string An unique identifier that can be used to cancel, enable or disable the watcher.
     */
    public function onReadable($stream, callable $callback, $data = null): string
    {
        /** @var Watcher<resource> $watcher */
        $watcher = new Watcher();
        $watcher->type = Watcher::READABLE;
        /** @psalm-suppress StringIncrement */
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $stream;
        $watcher->data = $data;

        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;

        return $watcher->id;
    }

    /**
     * Execute a callback when a stream resource becomes writable or is closed for writing.
     *
     * Warning: Closing resources locally, e.g. with `fclose`, might not invoke the callback. Be sure to `cancel` the
     * watcher when closing the resource locally. Drivers MAY choose to notify the user if there are watchers on invalid
     * resources, but are not required to, due to the high performance impact. Watchers on closed resources are
     * therefore undefined behavior.
     *
     * Multiple watchers on the same stream MAY be executed in any order.
     *
     * The created watcher MUST immediately be marked as enabled, but only be activated (i.e. callback can be called)
     * right before the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @template T
     *
     * @param resource $stream The stream to monitor.
     * @param callable(string, resource, T) $callback The callback to execute.
     * @param T $data Arbitrary data given to the callback function as the `$data` parameter.
     *
     * @return string An unique identifier that can be used to cancel, enable or disable the watcher.
     */
    public function onWritable($stream, callable $callback, $data = null): string
    {
        /** @var Watcher<resource> $watcher */
        $watcher = new Watcher();
        $watcher->type = Watcher::WRITABLE;
        /** @psalm-suppress StringIncrement */
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $stream;
        $watcher->data = $data;

        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;

        return $watcher->id;
    }

    /**
     * Enable a watcher to be active starting in the next tick.
     *
     * Watchers MUST immediately be marked as enabled, but only be activated (i.e. callbacks can be called) right before
     * the next tick. Callbacks of watchers MUST NOT be called in the tick they were enabled.
     *
     * @param string $watcherId The watcher identifier.
     *
     * @throws Psl\Exception\InvariantViolationException If watcher identifier is invalid.
     */
    public function enable(string $watcherId): void
    {
        Psl\invariant(
            isset($this->watchers[$watcherId]),
            'cannot enable an invalid watcher identifier "%s".',
            $watcherId
        );

        $watcher = $this->watchers[$watcherId];

        if ($watcher->enabled) {
            return; // Watcher already enabled.
        }

        $watcher->enabled = true;

        switch ($watcher->type) {
            case Watcher::DEFER:
                $this->nextTickQueue[$watcher->id] = $watcher;
                break;

            case Watcher::REPEAT:
            case Watcher::DELAY:
                $watcher->expiration = $this->now() + (int) $watcher->value;
                $this->enableQueue[$watcher->id] = $watcher;
                break;

            default:
                $this->enableQueue[$watcher->id] = $watcher;
                break;
        }
    }

    /**
     * Cancel a watcher.
     *
     * This will detach the event loop from all resources that are associated to the watcher. After this operation the
     * watcher is permanently invalid. Calling this function MUST NOT fail, even if passed an invalid watcher.
     *
     * @param string $watcherId The watcher identifier.
     */
    public function cancel(string $watcherId): void
    {
        $this->disable($watcherId);
        unset($this->watchers[$watcherId]);
    }

    /**
     * Disable a watcher immediately.
     *
     * A watcher MUST be disabled immediately, e.g. if a defer watcher disables a later defer watcher, the second defer
     * watcher isn't executed in this tick.
     *
     * Disabling a watcher MUST NOT invalidate the watcher. Calling this function MUST NOT fail, even if passed an
     * invalid watcher.
     *
     * @param string $watcherId The watcher identifier.
     */
    public function disable(string $watcherId): void
    {
        if (!isset($this->watchers[$watcherId])) {
            return;
        }

        $watcher = $this->watchers[$watcherId];

        if (!$watcher->enabled) {
            return; // Watcher already disabled.
        }

        $watcher->enabled = false;
        $id = $watcher->id;

        switch ($watcher->type) {
            case Watcher::DEFER:
                if (isset($this->nextTickQueue[$id])) {
                    // Watcher was only queued to be enabled.
                    unset($this->nextTickQueue[$id]);
                } else {
                    unset($this->deferQueue[$id]);
                }
                break;

            default:
                if (isset($this->enableQueue[$id])) {
                    // Watcher was only queued to be enabled.
                    unset($this->enableQueue[$id]);
                } else {
                    $this->deactivate($watcher);
                }
                break;
        }
    }

    /**
     * Reference a watcher.
     *
     * This will keep the event loop alive whilst the watcher is still being monitored. Watchers have this state by
     * default.
     *
     * @param string $watcherId The watcher identifier.
     *
     * @throws Psl\Exception\InvariantViolationException If watcher identifier is invalid.
     */
    public function reference(string $watcherId): void
    {
        Psl\invariant(
            isset($this->watchers[$watcherId]),
            'cannot enable an invalid watcher identifier "%s".',
            $watcherId
        );

        $this->watchers[$watcherId]->referenced = true;
    }

    /**
     * Unreference a watcher.
     *
     * The event loop should exit the run method when only unreferenced watchers are still being monitored. Watchers
     * are all referenced by default.
     *
     * @param string $watcherId The watcher identifier.
     */
    public function unreference(string $watcherId): void
    {
        if (!isset($this->watchers[$watcherId])) {
            return;
        }

        $this->watchers[$watcherId]->referenced = false;
    }

    /**
     * Stores information in the loop bound registry.
     *
     * Stored information is package private. Packages MUST NOT retrieve the stored state of other packages. Packages
     * MUST use their namespace as prefix for keys. They may do so by using `SomeClass::class` as key.
     *
     * If packages want to expose loop bound state to consumers other than the package, they SHOULD provide a dedicated
     * interface for that purpose instead of sharing the storage key.
     *
     * @param string $key The namespaced storage key.
     * @param mixed $value The value to be stored.
     */
    final public function setState(string $key, $value): void
    {
        if ($value === null) {
            unset($this->registry[$key]);
        } else {
            $this->registry[$key] = $value;
        }
    }

    /**
     * Gets information stored bound to the loop.
     *
     * Stored information is package private. Packages MUST NOT retrieve the stored state of other packages. Packages
     * MUST use their namespace as prefix for keys. They may do so by using `SomeClass::class` as key.
     *
     * If packages want to expose loop bound state to consumers other than the package, they SHOULD provide a dedicated
     * interface for that purpose instead of sharing the storage key.
     *
     * @param string $key The namespaced storage key.
     *
     * @return mixed The previously stored value or `null` if it doesn't exist.
     */
    final public function getState(string $key)
    {
        return isset($this->registry[$key]) ? $this->registry[$key] : null;
    }

    /**
     * Set a callback to be executed when an error occurs.
     *
     * The callback receives the error as the first and only parameter. The return value of the callback gets ignored.
     * If it can't handle the error, it MUST throw the error. Errors thrown by the callback or during its invocation
     * MUST be thrown into the `run` loop and stop the driver.
     *
     * Subsequent calls to this method will overwrite the previous handler.
     *
     * @return callable(\Throwable $error):void|null The previous handler, `null` if there was none.
     */
    public function setErrorHandler(callable $callback = null): ?callable
    {
        $previous = $this->errorHandler;
        $this->errorHandler = $callback;
        return $previous;
    }

    /**
     * Returns the current loop time in millisecond increments. Note this value does not necessarily correlate to
     * wall-clock time, rather the value returned is meant to be used in relative comparisons to prior values returned
     * by this method (intervals, expiration calculations, etc.) and is only updated once per loop tick.
     *
     * Extending classes should override this function to return a value cached once per loop tick.
     */
    public function now(): int
    {
        /** @psalm-suppress InvalidOperand */
        return (int) (microtime(true) * self::MILLISEC_PER_SEC);
    }

    /**
     * Removes all watchers, registry data, and error handler from the event loop. This method is intended for
     * clearing the loop between tests and not intended for use in an application.
     */
    final public function clear(): void
    {
        foreach ($this->watchers as $watcher) {
            $this->cancel($watcher->id);
        }

        $this->registry = [];
        $this->errorHandler = null;
    }

    /**
     * Activates (enables) all the given watchers.
     *
     * @param array<string, Watcher<int>|Watcher<resource>|Watcher<null>> $watchers
     */
    abstract protected function activate(array $watchers): void;

    /**
     * Dispatches any pending read/write, timer, and signal events.
     */
    abstract protected function dispatch(bool $blocking): void;

    /**
     * Deactivates (disables) the given watcher.
     *
     * @param Watcher<int>|Watcher<resource>|Watcher<null> $watcher
     */
    abstract protected function deactivate(Watcher $watcher): void;

    /**
     * Invokes the error handler with the given exception.
     *
     * @param Throwable $exception The exception thrown from a watcher callback.
     *
     * @throws Throwable If no error handler has been set.
     */
    protected function error(Throwable $exception): void
    {
        if ($this->errorHandler === null) {
            throw $exception;
        }

        ($this->errorHandler)($exception);
    }

    /**
     * @return bool True if no enabled and referenced watchers remain in the loop.
     */
    private function isEmpty(): bool
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->enabled && $watcher->referenced) {
                return false;
            }
        }

        return true;
    }

    /**
     * Executes a single tick of the event loop.
     */
    public function tick(): void
    {
        if (empty($this->deferQueue)) {
            $this->deferQueue = $this->nextTickQueue;
        } else {
            $this->deferQueue = array_merge($this->deferQueue, $this->nextTickQueue);
        }
        $this->nextTickQueue = [];

        $this->activate($this->enableQueue);
        $this->enableQueue = [];

        foreach ($this->deferQueue as $watcher) {
            if (!isset($this->deferQueue[$watcher->id])) {
                continue; // Watcher disabled by another defer watcher.
            }

            unset($this->watchers[$watcher->id], $this->deferQueue[$watcher->id]);

            try {
                $watcher->execute();
            } catch (Throwable $exception) {
                $this->error($exception);
            }
        }

        /** @psalm-suppress RedundantCondition */
        $this->dispatch(
            empty($this->nextTickQueue)
                && empty($this->enableQueue)
                && $this->running
                && !$this->isEmpty()
        );
    }
}
