<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Psl\Async\Exception\CancelledException;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_key_exists;
use function array_search;
use function array_shift;
use function array_splice;
use function count;

/**
 * Run an operation with a limit on number of ongoing asynchronous jobs of 1.
 *
 * Just like {@see KeyedSemaphore}, all operations must have the same input type (Tin) and output type (Tout), and be processed by the same function;
 *
 * @template Tk of array-key
 * @template Tin
 * @template Tout
 *
 * @see KeyedSemaphore
 *
 * @mago-expect lint:excessive-nesting
 */
final class KeyedSequence
{
    /**
     * @var array<Tk, bool>
     */
    private array $ongoing = [];

    /**
     * @var array<Tk, list<Suspension>>
     */
    private array $pending = [];

    /**
     * @var array<Tk, list<Suspension>>
     */
    private array $waits = [];

    /**
     * @param (Closure(Tk, Tin): Tout) $operation
     */
    public function __construct(
        private readonly Closure $operation,
    ) {}

    /**
     * Run the operation using the given `$input`, after all previous operations have completed.
     *
     * @param Tk $key
     * @param Tin $input
     *
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     *
     * @return Tout
     *
     * @see Sequence::cancel()
     */
    public function waitFor(
        string|int $key,
        mixed $input,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): mixed {
        if (array_key_exists($key, $this->ongoing)) {
            if ($cancellation->cancellable) {
                $cancellation->throwIfCancelled();
            }

            $suspension = EventLoop::getSuspension();
            $this->pending[$key][] = $suspension;

            $id = null;
            if ($cancellation->cancellable) {
                $id = $cancellation->subscribe(function (CancelledException $e) use ($key, $suspension): void {
                    $index = array_search($suspension, $this->pending[$key] ?? [], true);
                    if (false !== $index) {
                        array_splice($this->pending[$key], $index, 1);
                        if ([] === $this->pending[$key]) {
                            unset($this->pending[$key]);
                        }

                        $suspension->throw($e);
                    }
                });
            }

            try {
                $suspension->suspend();
            } finally {
                if (null !== $id) {
                    $cancellation->unsubscribe($id);
                }
            }
        }

        $this->ongoing[$key] = true;

        try {
            return ($this->operation)($key, $input);
        } finally {
            $this->pending[$key] ??= [];
            $suspension = array_shift($this->pending[$key]);
            if ([] === $this->pending[$key]) {
                unset($this->pending[$key]);
            }

            if (null !== $suspension) {
                $suspension->resume();
            } else {
                foreach ($this->waits[$key] ?? [] as $suspension) {
                    $suspension->resume();
                }

                unset($this->waits[$key], $this->ongoing[$key]);
            }
        }
    }

    /**
     * Cancel pending operations for the given key.
     *
     * Any pending operation will fail with the given exception.
     *
     * Future operations will continue execution as usual.
     *
     * @param Tk $key
     */
    public function cancel(string|int $key, Exception $exception): void
    {
        $suspensions = $this->pending[$key] ?? [];
        unset($this->pending[$key]);
        foreach ($suspensions as $suspension) {
            $suspension->throw($exception);
        }
    }

    /**
     * Cancel all pending operations.
     *
     * Pending operation will fail with the given exception.
     *
     * Future operations will continue execution as usual.
     */
    public function cancelAll(Exception $exception): void
    {
        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $suspensions) {
            foreach ($suspensions as $suspension) {
                $suspension->throw($exception);
            }
        }
    }

    /**
     * Get the number of operations pending execution for the given key.
     *
     * @param Tk $key
     *
     * @return int<0, max>
     */
    public function getPendingOperations(string|int $key): int
    {
        return count($this->pending[$key] ?? []);
    }

    /**
     * Get the number of total operations pending execution.
     *
     * @return int<0, max>
     */
    public function getTotalPendingOperations(): int
    {
        $count = 0;
        foreach ($this->pending as $suspensions) {
            $count += count($suspensions);
        }

        /** @var int<0, max> */
        return $count;
    }

    /**
     * Check if there's any operations pending execution for the given key.
     *
     * If this method returns `true`, it means the sequence is busy, future calls to `waitFor` will wait.
     *
     * @param Tk $key
     */
    public function hasPendingOperations(string|int $key): bool
    {
        return array_key_exists($key, $this->pending);
    }

    /**
     * Check if there's any operations pending execution.
     */
    public function hasAnyPendingOperations(): bool
    {
        return [] !== $this->pending;
    }

    /**
     * Check if there's an ongoing operation for the given key.
     *
     * If this method returns `true`, it means the sequence is busy, future calls to `waitFor` will wait.
     * If this method returns `false`, it means the sequence is not busy, future calls to `waitFor` will execute immediately.
     *
     * @param Tk $key
     */
    public function hasOngoingOperations(string|int $key): bool
    {
        return array_key_exists($key, $this->ongoing);
    }

    /**
     * Check if the sequence has any ongoing operations.
     */
    public function hasAnyOngoingOperations(): bool
    {
        return [] !== $this->ongoing;
    }

    /**
     * Get the number of total ongoing operations.
     *
     * @return int<0, max>
     */
    public function getTotalOngoingOperations(): int
    {
        return count($this->ongoing);
    }

    /**
     * Wait for all pending operations associated with the given key to finish execution.
     *
     * If the sequence does not have any ongoing operations for the given key, this method will return immediately.
     *
     * @param Tk $key
     *
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function waitForPending(
        string|int $key,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        if (!array_key_exists($key, $this->ongoing)) {
            return;
        }

        if ($cancellation->cancellable) {
            $cancellation->throwIfCancelled();
        }

        $suspension = EventLoop::getSuspension();
        $this->waits[$key][] = $suspension;

        $id = null;
        if ($cancellation->cancellable) {
            $id = $cancellation->subscribe(function (CancelledException $e) use ($key, $suspension): void {
                $index = array_search($suspension, $this->waits[$key] ?? [], true);
                if (false !== $index) {
                    array_splice($this->waits[$key], $index, 1);
                    if ([] === $this->waits[$key]) {
                        unset($this->waits[$key]);
                    }

                    $suspension->throw($e);
                }
            });
        }

        try {
            $suspension->suspend();
        } finally {
            if (null !== $id) {
                $cancellation->unsubscribe($id);
            }
        }
    }
}
