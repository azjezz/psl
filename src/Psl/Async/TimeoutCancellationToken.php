<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl\DateTime\Duration;
use Revolt\EventLoop;

/**
 * A cancellation token that automatically cancels after a given duration.
 */
final class TimeoutCancellationToken implements CancellationTokenInterface
{
    private bool $cancelled = false;

    private null|Exception\CancelledException $exception = null;

    /**
     * @var array<string, (Closure(Exception\CancelledException): void)>
     */
    private array $callbacks = [];

    private readonly string $watcher;

    public function __construct(Duration $timeout)
    {
        $this->watcher = EventLoop::delay(max($timeout->getTotalSeconds(), 0.0), function (): void {
            $this->cancelled = true;
            $this->exception = new Exception\CancelledException($this, new Exception\TimeoutException());

            $exception = $this->exception;
            $callbacks = $this->callbacks;
            $this->callbacks = [];

            foreach ($callbacks as $callback) {
                $callback($exception);
            }
        });

        EventLoop::unreference($this->watcher);
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

    public function __destruct()
    {
        EventLoop::cancel($this->watcher);
    }
}
