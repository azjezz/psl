<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_shift;
use function count;

/**
 * Run an operation with a limit on number of ongoing asynchronous jobs.
 *
 * All operations must have the same input type (Tin) and output type (Tout), and be processed by the same function;
 * `Tin` may be a callable invoked by the `$operation` for maximum flexibility,
 * however this pattern is best avoided in favor of creating semaphores with a more narrow process.
 *
 * @template Tin
 * @template Tout
 */
final class Semaphore
{
    /**
     * @var int<0, max>
     */
    private int $ingoing = 0;

    /**
     * @var list<Suspension>
     */
    private array $pending = [];

    /**
     * @var list<Suspension>
     */
    private array $waits = [];

    /**
     * @param positive-int $concurrencyLimit
     * @param (Closure(Tin): Tout) $operation
     */
    public function __construct(
        private readonly int $concurrencyLimit,
        private readonly Closure $operation,
    ) {
    }

    /**
     * Run the operation using the given `$input`.
     *
     * If the concurrency limit has been reached, this method will wait until one of the ingoing operations has completed.
     *
     * @param Tin $input
     *
     * @return Tout
     *
     * @see Semaphore::cancel()
     */
    public function waitFor(mixed $input): mixed
    {
        if ($this->ingoing === $this->concurrencyLimit) {
            $this->pending[] = $suspension = EventLoop::getSuspension();

            $suspension->suspend();
        }

        $this->ingoing++;

        try {
            return ($this->operation)($input);
        } finally {
            $suspension = array_shift($this->pending);
            if ($suspension !== null) {
                $suspension->resume();
            } else {
                foreach ($this->waits as $suspension) {
                    $suspension->resume();
                }
                $this->waits = [];
            }

            $this->ingoing--;
        }
    }

    /**
     * Cancel all pending operations.
     *
     * Any pending operation will fail with the given exception.
     *
     * Future operations will continue execution as usual.
     */
    public function cancel(Exception $exception): void
    {
        $suspensions = $this->pending;
        $this->pending = [];
        foreach ($suspensions as $suspension) {
            $suspension->throw($exception);
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
     * Get the number of operations pending execution.
     *
     * @return int<0, max>
     */
    public function getPendingOperations(): int
    {
        /** @var int<0, max> */
        return count($this->pending);
    }

    /**
     * Check if there's any operations pending execution.
     *
     * If this method returns `true`, it means the semaphore is full, future calls to `waitFor` will wait.
     */
    public function hasPendingOperations(): bool
    {
        return $this->getPendingOperations() > 0;
    }

    /**
     * Get the number of ingoing operations.
     *
     * The returned number will always be lower, or equal to the concurrency limit.
     *
     * @return int<0, max>
     */
    public function getIngoingOperations(): int
    {
        return $this->ingoing;
    }

    /**
     * Check if the semaphore has any ingoing operations.
     *
     * If this method returns `true`, it does not mean future calls to `waitFor` will wait, since a semaphore can have multiple ingoing operations
     * at the same time.
     */
    public function hasIngoingOperations(): bool
    {
        return $this->ingoing > 0;
    }

    /**
     * Wait for all pending operations to finish execution.
     */
    public function waitForPending(): void
    {
        if ($this->ingoing !== $this->concurrencyLimit) {
            return;
        }

        $suspension = EventLoop::getSuspension();
        $this->waits[] = $suspension;
        $suspension->suspend();
    }
}
