<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

use function microtime;

/**
 * Manages optional incremental timeouts for asynchronous operations.
 *
 * This class provides a way to specify a timeout for an operation and
 * execute a handler function if the timeout is exceeded. It can be
 * particularly useful in asynchronous programming where operations
 * might need to be interrupted or handled differently if they take
 * too long to complete.
 */
final class OptionalIncrementalTimeout
{
    /**
     * @var ?float The end time in microseconds.
     */
    private ?float $end;

    /**
     * @var (Closure(): ?float) The handler to be called upon timeout.
     */
    private Closure $handler;

    /**
     * @param float|null $timeout The timeout duration in seconds. Null to disable timeout.
     * @param (Closure(): ?float) $handler The handler to be executed if the timeout is reached.
     */
    public function __construct(?float $timeout, Closure $handler)
    {
        $this->handler = $handler;

        $this->end = $timeout !== null ? (microtime(true) + $timeout) : null;
    }

    /**
     * Retrieves the remaining time until the timeout is reached, or null if no timeout is set.
     *
     * If the timeout has already been exceeded, the handler is invoked, and its return value is provided.
     *
     * @return float|null The remaining time in seconds, null if no timeout is set, or the handler's return value if the timeout is exceeded.
     */
    public function getRemaining(): ?float
    {
        if ($this->end === null) {
            return null;
        }

        $remaining = $this->end - microtime(true);

        return $remaining <= 0 ? ($this->handler)() : $remaining;
    }
}
