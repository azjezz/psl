<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception as RootException;

/**
 * A cancellation token that can be manually triggered.
 *
 * Call {@see cancel()} to cancel all subscribed operations.
 */
final class SignalCancellationToken implements CancellationTokenInterface
{
    public readonly bool $cancellable;

    private bool $cancelled = false;

    private null|Exception\CancelledException $exception = null;

    /**
     * @var array<string, (Closure(Exception\CancelledException): void)>
     */
    private array $callbacks = [];

    public function __construct()
    {
        $this->cancellable = true;
    }

    /**
     * Cancel all subscribed operations.
     *
     * @param null|RootException $cause Optional cause exception, attached as the previous exception on the {@see Exception\CancelledException}.
     */
    public function cancel(null|RootException $cause = null): void
    {
        if ($this->cancelled) {
            return;
        }

        $this->cancelled = true;
        $this->exception = new Exception\CancelledException($this, $cause);

        $exception = $this->exception;
        $callbacks = $this->callbacks;
        $this->callbacks = [];

        foreach ($callbacks as $callback) {
            $callback($exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Closure $callback): string
    {
        if ($this->cancelled) {
            /** @var Exception\CancelledException $exception - always set when cancelled */
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
