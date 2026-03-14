<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

/**
 * A cancellation token that is cancelled when either of two inner tokens is cancelled.
 *
 * This is useful for combining a request-scoped token with an operation-specific timeout:
 *
 *     $linked = new LinkedCancellationToken(
 *         $requestToken,
 *         new TimeoutCancellationToken(Duration::seconds(5)),
 *     );
 */
final class LinkedCancellationToken implements CancellationTokenInterface
{
    private bool $cancelled = false;

    private null|Exception\CancelledException $exception = null;

    /**
     * @var array<string, (Closure(Exception\CancelledException): void)>
     */
    private array $callbacks = [];

    /** @var non-empty-string */
    private readonly string $firstId;
    /** @var non-empty-string */
    private readonly string $secondId;

    public function __construct(
        private readonly CancellationTokenInterface $first,
        private readonly CancellationTokenInterface $second,
    ) {
        $handler = function (Exception\CancelledException $inner): void {
            if ($this->cancelled) {
                return;
            }

            $this->cancelled = true;
            $this->exception = $inner;

            $callbacks = $this->callbacks;
            $this->callbacks = [];

            foreach ($callbacks as $callback) {
                $callback($inner);
            }
        };

        $this->firstId = $first->subscribe($handler);
        $this->secondId = $second->subscribe($handler);
    }

    public function __destruct()
    {
        $this->first->unsubscribe($this->firstId);
        $this->second->unsubscribe($this->secondId);
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Closure $callback): string
    {
        if ($this->cancelled) {
            /** @var Exception\CancelledException $exception */
            $exception = $this->exception;
            $callback($exception);

            return 'null';
        }

        $id = Internal\next_id();
        $this->callbacks[$id] = $callback;

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(string $id): void
    {
        unset($this->callbacks[$id]);
    }

    /**
     * @inheritDoc
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @inheritDoc
     */
    public function throwIfCancelled(): void
    {
        if ($this->cancelled) {
            /** @var Exception\CancelledException $exception */
            $exception = $this->exception;

            throw $exception;
        }
    }
}
