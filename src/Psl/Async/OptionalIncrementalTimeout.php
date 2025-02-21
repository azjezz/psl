<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;

/**
 * Manages optional incremental timeouts for asynchronous operations.
 *
 * This class provides a way to specify a timeout for an operation and
 * execute a handler function if the timeout is exceeded. It can be
 * particularly useful in asynchronous programming where operations
 * might need to be interrupted or handled differently if they take
 * too long to complete.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class OptionalIncrementalTimeout
{
    /**
     * @var ?Timestamp The end time.
     */
    private null|Timestamp $end;

    /**
     * @var (Closure(): ?Duration) The handler to be called upon timeout.
     */
    private Closure $handler;

    /**
     * @param null|Duration $timeout The timeout duration. Null to disable timeout.
     * @param (Closure(): ?Duration) $handler The handler to be executed if the timeout is reached.
     */
    public function __construct(null|Duration $timeout, Closure $handler)
    {
        $this->handler = $handler;

        if (null === $timeout) {
            $this->end = null;

            return;
        }

        if (!$timeout->isPositive()) {
            $this->end = Timestamp::monotonic();
            return;
        }

        $this->end = Timestamp::monotonic()->plus($timeout);
    }

    /**
     * Retrieves the remaining time until the timeout is reached, or null if no timeout is set.
     *
     * If the timeout has already been exceeded, the handler is invoked, and its return value is provided.
     *
     * @return Duration|null The remaining time duration, null if no timeout is set, or the handler's return value if the timeout is exceeded.
     */
    public function getRemaining(): null|Duration
    {
        if ($this->end === null) {
            return null;
        }

        $remaining = $this->end->since(Timestamp::monotonic());

        return $remaining->isPositive() ? $remaining : ($this->handler)();
    }
}
