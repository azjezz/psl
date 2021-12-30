<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
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
    private bool $busy = false;

    /**
     * @var list<Suspension>
     */
    private array $suspensions = [];

    /**
     * @param (Closure(Tin): Tout) $operation
     */
    public function __construct(
        private Closure $operation,
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
        if ($this->busy) {
            $this->suspensions[] = $suspension = Scheduler::createSuspension();

            $suspension->suspend();
        }

        $this->busy = true;

        try {
            return ($this->operation)($input);
        } finally {
            $suspension = $this->suspensions[0] ?? null;
            if ($suspension !== null) {
                $this->suspensions = array_slice($this->suspensions, 1);
                $suspension->resume();
            } else {
                $this->busy = false;
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
        $suspensions = $this->suspensions;
        $this->suspensions = [];
        foreach ($suspensions as $suspension) {
            $suspension->throw($exception);
        }
    }

    public function isBusy(): bool
    {
        return $this->busy;
    }
}
