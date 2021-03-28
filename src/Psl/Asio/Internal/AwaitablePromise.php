<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Amp\Promise;
use Psl\Asio\Awaitable;

/**
 * @template T
 *
 * @template-implements Awaitable<T>
 *
 * @internal
 */
final class AwaitablePromise implements Awaitable
{
    private Promise $promise;
    private ?bool $failed = null;

    /**
     * @param Promise<T>
     */
    public function __construct(
        Promise $promise
    ) {
        $this->promise = $promise;
        $this->promise->onResolve(function ($throwable, $result): void {
            if ($throwable) {
                $this->failed = true;
            } else {
                $this->failed = false;
            }
        });
    }

    public function onJoin(callable $callback): void
    {
        $this->promise->onResolve($callback);
    }

    public function isSucceeded(): bool
    {
        return $this->failed === false;
    }

    public function isFinished(): bool
    {
        return $this->failed !== null;
    }

    public function isFailed(): bool
    {
        return $this->failed === true;
    }
}
