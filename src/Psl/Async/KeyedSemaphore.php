<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_key_exists;
use function array_shift;
use function array_sum;
use function count;

/**
 * Run an operation with a limit on number of ongoing asynchronous jobs for a specific key.
 *
 * All operations must have the same input type (Tin) and output type (Tout), and be processed by the same function.
 *
 * `Tin` may be a callable invoked by the `$operation` for maximum flexibility,
 * however this pattern is best avoided in favor of creating semaphores with a more narrow process.
 *
 * @template Tk of array-key
 * @template Tin
 * @template Tout
 */
final class KeyedSemaphore
{
    /**
     * @var array<Tk, int<0, max>>
     */
    private array $ingoing = [];

    /**
     * @var array<Tk, list<Suspension>>
     */
    private array $pending = [];

    /**
     * @var array<Tk, list<Suspension>>
     */
    private array $waits = [];

    /**
     * @param positive-int $concurrencyLimit
     * @param (Closure(Tk, Tin): Tout) $operation
     */
    public function __construct(
        private readonly int $concurrencyLimit,
        private readonly Closure $operation,
    ) {
    }

    /**
     * Run the operation using the given `$input`.
     *
     * If the concurrency limit has been reached for the given `$key`, this method will wait until one of the ingoing operations has completed.
     *
     * @param Tk $key
     * @param Tin $input
     *
     * @return Tout
     *
     * @see Semaphore::cancel()
     */
    public function waitFor(string|int $key, mixed $input): mixed
    {
        $this->ingoing[$key] = $this->ingoing[$key] ?? 0;
        if ($this->ingoing[$key] === $this->concurrencyLimit) {
            $this->pending[$key][] = $suspension = EventLoop::getSuspension();

            $suspension->suspend();
        }

        $this->ingoing[$key]++;

        try {
            return ($this->operation)($key, $input);
        } finally {
            if (($this->pending[$key] ?? []) !== []) {
                $suspension = array_shift($this->pending[$key]);
                if ([] === $this->pending[$key]) {
                    unset($this->pending[$key]);
                }

                $suspension->resume();

                /** @psalm-suppress InvalidPropertyAssignmentValue */
                $this->ingoing[$key]--;
            } else {
                foreach ($this->waits[$key] ?? [] as $suspension) {
                    $suspension->resume();
                }

                unset($this->waits[$key]);

                /** @psalm-suppress InvalidPropertyAssignmentValue */
                $this->ingoing[$key]--;
                if ($this->ingoing[$key] === 0) {
                    unset($this->ingoing[$key]);
                }
            }
        }
    }

    /**
     * Cancel pending operations for the given key.
     *
     * Pending operation will fail with the given exception.
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
     * Get the concurrency limit of the semaphore.
     *
     * @return positive-int
     */
    public function getConcurrencyLimit(): int
    {
        return $this->concurrencyLimit;
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
        /** @var int<0, max> */
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
     * If this method returns `true`, it means the semaphore has reached it's limits, future calls to `waitFor` will wait.
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
        return $this->pending !== [];
    }

    /**
     * Get the number of ingoing operations for the given key.
     *
     * The returned number will always be lower, or equal to the concurrency limit.
     *
     * @param Tk $key
     *
     * @return int<0, max>
     */
    public function getIngoingOperations(string|int $key): int
    {
        return $this->ingoing[$key] ?? 0;
    }

    /**
     * Get the number of total ingoing operations.
     *
     * The returned number can be higher than the concurrency limit, as it is the sum of all ingoing operations using different keys.
     *
     * @return int<0, max>
     */
    public function getTotalIngoingOperations(): int
    {
        /** @var int<0, max> */
        return array_sum($this->ingoing);
    }

    /**
     * Check if the semaphore has any ingoing operations for the given key.
     *
     * If this method returns `true`, it does not mean future calls to `waitFor` will wait, since a semaphore can have multiple ingoing operations
     * at the same time for the same key.
     *
     * @param Tk $key
     */
    public function hasIngoingOperations(string|int $key): bool
    {
        return array_key_exists($key, $this->ingoing);
    }

    /**
     * Check if the semaphore has any ingoing operations.
     */
    public function hasAnyIngoingOperations(): bool
    {
        return $this->ingoing !== [];
    }

    /**
     * Wait for all pending operations associated with the given key to start execution.
     *
     * If the semaphore is has not reached the concurrency limit the given key, this method will return immediately.
     *
     * @param Tk $key
     */
    public function waitForPending(string|int $key): void
    {
        if (($this->ingoing[$key] ?? 0) !== $this->concurrencyLimit) {
            return;
        }

        $suspension = EventLoop::getSuspension();
        $this->waits[$key][] = $suspension;
        $suspension->suspend();
    }
}
