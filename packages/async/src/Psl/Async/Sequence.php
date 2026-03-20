<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Psl\Async\Exception\CancelledException;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_search;
use function array_shift;
use function array_splice;
use function count;

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
    private bool $ongoing = false;

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
    ) {}

    /**
     * Run the operation using the given `$input`, after all previous operations have completed.
     *
     * @param Tin $input
     *
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     *
     * @return Tout
     *
     * @see Sequence::cancel()
     */
    public function waitFor(mixed $input, CancellationTokenInterface $cancellation = new NullCancellationToken()): mixed
    {
        if ($this->ongoing) {
            if ($cancellation->cancellable) {
                $cancellation->throwIfCancelled();
            }

            $suspension = EventLoop::getSuspension();
            $this->pending[] = $suspension;

            $id = null;
            if ($cancellation->cancellable) {
                $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                    $index = array_search($suspension, $this->pending, true);
                    if (false !== $index) {
                        array_splice($this->pending, $index, 1);
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

        $this->ongoing = true;

        try {
            return ($this->operation)($input);
        } finally {
            $suspension = array_shift($this->pending);
            if (null !== $suspension) {
                $suspension->resume();
            } else {
                foreach ($this->waits as $suspension) {
                    $suspension->resume();
                }

                $this->waits = [];

                $this->ongoing = false;
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
        return count($this->pending);
    }

    /**
     * Check if there's any operations pending execution.
     *
     * If this method returns `true`, it means future calls to `waitFor` will wait.
     */
    public function hasPendingOperations(): bool
    {
        return [] !== $this->pending;
    }

    /**
     * Check if the sequence has any ongoing operations.
     *
     * If this method returns `true`, it means future calls to `waitFor` will wait.
     * If this method returns `false`, it means future calls to `waitFor` will execute immediately.
     */
    public function hasOngoingOperations(): bool
    {
        return $this->ongoing;
    }

    /**
     * Wait for all pending operations to finish execution.
     *
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function waitForPending(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if (!$this->ongoing) {
            return;
        }

        if ($cancellation->cancellable) {
            $cancellation->throwIfCancelled();
        }

        $suspension = EventLoop::getSuspension();
        $this->waits[] = $suspension;

        $id = null;
        if ($cancellation->cancellable) {
            $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                $index = array_search($suspension, $this->waits, true);
                if (false !== $index) {
                    array_splice($this->waits, $index, 1);
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
