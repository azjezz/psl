<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_slice;

/**
 * Run an operation with a limit on number of ongoing asynchronous jobs of 1.
 *
 * Just like {@see Semaphore}, all operations must have the same input type (Tin) and output type (Tout), and be processed by the same function;
 *
 * @template Tin
 * @template Tout
 *
 * @see Semaphore
 */
final class Sequence
{
    private bool $ingoing = false;

    /**
     * @var list<Suspension>
     */
    private array $pending = [];

    /**
     * @var list<Suspension>
     */
    private array $waits = [];

    /**
     * @param (Closure(Tin): Tout) $operation
     */
    public function __construct(
        private readonly Closure $operation,
    ) {
    }

    /**
     * Run the operation using the given `$input`, after all previous operations have completed.
     *
     * @param Tin $input
     *
     * @return Tout
     *
     * @see Sequence::cancel()
     */
    public function waitFor(mixed $input): mixed
    {
        if ($this->ingoing) {
            $this->pending[] = $suspension = EventLoop::getSuspension();

            $suspension->suspend();
        }

        $this->ingoing = true;

        try {
            return ($this->operation)($input);
        } finally {
            $suspension = $this->pending[0] ?? null;
            if ($suspension !== null) {
                $this->pending = array_slice($this->pending, 1);
                $suspension->resume();
            } else {
                foreach ($this->waits as $suspension) {
                    $suspension->resume();
                }
                $this->waits = [];

                $this->ingoing = false;
            }
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
     * If this method returns `true`, it means future calls to `waitFor` will wait.
     */
    public function hasPendingOperations(): bool
    {
        return $this->pending !== [];
    }

    /**
     * Check if the sequence has any ingoing operations.
     *
     * If this method returns `true`, it means future calls to `waitFor` will wait.
     * If this method returns `false`, it means future calls to `waitFor` will execute immediately.
     */
    public function hasIngoingOperations(): bool
    {
        return $this->ingoing;
    }

    /**
     * Wait for all pending operations to finish execution.
     */
    public function waitForPending(): void
    {
        if (!$this->ingoing) {
            return;
        }

        $suspension = EventLoop::getSuspension();
        $this->waits[] = $suspension;
        $suspension->suspend();
    }
}
