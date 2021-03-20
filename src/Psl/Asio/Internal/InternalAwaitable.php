<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl\Asio\Awaitable;

/**
 * @template T
 *
 * @template-implements Awaitable<T>
 *
 * @internal
 */
final class InternalAwaitable implements Awaitable
{
    /**
     * @var WaitHandle<T>
     */
    private WaitHandle $handle;

    /**
     * @param WaitHandle<T> $handle
     */
    public function __construct(
        WaitHandle $handle
    ) {
        $this->handle = $handle;
    }

    /**
     * @inheritDoc
     */
    public function onJoin(callable $onJoin): void
    {
        $this->handle->onJoin($onJoin);
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
