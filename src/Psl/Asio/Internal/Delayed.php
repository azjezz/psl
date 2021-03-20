<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl\Asio\Awaitable;
use Throwable;

/**
 * Creates an awaitable handle that finishes itself with a given value after a number of milliseconds.
 *
 * @implements Awaitable<void>
 */
final class Delayed implements Awaitable
{
    private WaitHandle $handle;

    private ?string $watcher = null;

    /**
     * @param int $time Milliseconds before finishing the awaitable.
     */
    public function __construct(int $time)
    {
        $this->handle = new WaitHandle();
        $placeholder = $this->handle;

        $watcher = &$this->watcher;
        $this->watcher = EventLoop::delay($time, static function () use (&$watcher, $placeholder): void {
            $watcher = null;
            $placeholder->finish((static function (): void {
              // noop.
            })());
        }, null);
    }

    public function __destruct()
    {
        if ($this->watcher !== null) {
            EventLoop::cancel($this->watcher);
        }
    }

    public function onJoin(callable $onJoin): void
    {
        $this->handle->onJoin(
            static function (?Throwable $exception) use ($onJoin) {
                return $onJoin($exception, null);
            }
        );
    }

    public function isSucceeded(): bool
    {
        return $this->handle->isFinished() && !$this->handle->isFailed();
    }

    public function isFinished(): bool
    {
        return $this->handle->isFinished();
    }

    public function isFailed(): bool
    {
        return $this->handle->isFailed();
    }
}
