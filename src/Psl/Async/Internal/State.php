<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use Psl;
use Psl\Async\Awaitable;
use Psl\Async\Exception;
use Psl\Async\Scheduler;
use Throwable;

/**
 * @internal
 *
 * @template T
 *
 * @codeCoverageIgnore
 */
final class State
{
    // Static so they can be used as array keys
    private static string $nextId = 'a';

    private bool $complete = false;

    private bool $handled = false;

    /**
     * @var array<string, (callable(?Throwable, ?T, string): void)>
     */
    private array $callbacks = [];

    /**
     * @var T|null
     */
    private mixed $result = null;

    private ?Throwable $throwable = null;

    public function __destruct()
    {
        if ($this->throwable && !$this->handled) {
            $throwable = Exception\UnhandledAwaitableException::forThrowable($this->throwable);
            Scheduler::queue(static fn () => throw $throwable);
        }
    }

    /**
     * Registers a callback to be notified once the operation is complete or errored.
     *
     * The callback is invoked directly from the event loop context, so suspension within the callback is not possible.
     *
     * @param (callable(?Throwable, ?T, string): void) $callback Callback invoked on completion of the awaitable.
     *
     * @return string Identifier that can be used to cancel interest for this awaitable.
     */
    public function subscribe(callable $callback): string
    {
        /** @psalm-suppress StringIncrement */
        $id = self::$nextId++;

        $this->handled = true;

        if ($this->complete) {
            Scheduler::queue(fn() => $callback($this->throwable, $this->result, $id));
        } else {
            $this->callbacks[$id] = $callback;
        }

        return $id;
    }

    /**
     * Cancels a subscription.
     *
     * Cancellations are advisory only. The callback might still be called if it is already queued for execution.
     *
     * @param string $id Identifier returned from subscribe()
     */
    public function unsubscribe(string $id): void
    {
        unset($this->callbacks[$id]);
    }

    /**
     * Completes the operation with a result value.
     *
     * @param T $result Result of the operation.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     * @throws Psl\Exception\InvariantViolationException If $result is an instance of {@see Awaitable}.
     */
    public function complete(mixed $result): void
    {
        Psl\invariant(!$this->complete, 'Operation is no longer pending.');
        Psl\invariant(!$result instanceof Awaitable, 'Cannot complete with an instance of ' . Awaitable::class);

        $this->result = $result;
        $this->invokeCallbacks();
    }

    /**
     * Marks the operation as failed.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     */
    public function error(Throwable $throwable): void
    {
        Psl\invariant(!$this->complete, 'Operation is no longer pending.');

        $this->throwable = $throwable;
        $this->invokeCallbacks();
    }

    /**
     * Suppress the exception thrown to the loop error handler if and operation error is not handled by a callback.
     */
    public function ignore(): void
    {
        $this->handled = true;
    }

    /**
     * @return bool True if the operation has completed.
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    private function invokeCallbacks(): void
    {
        $this->complete = true;

        foreach ($this->callbacks as $id => $callback) {
            Scheduler::queue(fn() => $callback($this->throwable, $this->result, $id));
        }

        $this->callbacks = [];
    }
}
