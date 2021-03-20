<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 *
 * @template-implements Awaitable<T>
 *
 * @internal
 */
final class FailedAwaitable implements Awaitable
{
    private Throwable $exception;

    /**
     * @param Throwable $exception Rejection reason.
     */
    public function __construct(
        Throwable $exception
    ) {
        $this->exception = $exception;
    }

    public function onJoin(callable $onJoin): void
    {
        EventLoop::defer(
            function () use ($onJoin) {
                $onJoin($this->exception, null);
            },
            null
        );
    }

    public function isSucceeded(): bool
    {
        return false;
    }

    public function isFinished(): bool
    {
        return true;
    }

    public function isFailed(): bool
    {
        return true;
    }
}
