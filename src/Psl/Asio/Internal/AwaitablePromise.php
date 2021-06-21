<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Amp;
use Amp\Promise;
use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 *
 * @template-implements Awaitable<T>
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class AwaitablePromise implements Awaitable
{
    private Promise $promise;
    private ?bool $failed = null;

    /**
     * @param Promise<T> $promise
     */
    public function __construct(
        Promise $promise
    ) {
        $this->promise = $promise;
        $this->promise->onResolve(function (?Throwable $throwable, $_result): Promise {
            if ($throwable) {
                $this->failed = true;
            } else {
                $this->failed = false;
            }

            return new Amp\Success();
        });
    }

    /**
     * @psalm-suppress InvalidArgument,MixedArgument,MixedAssignment
     */
    public function onJoin(callable $callback): void
    {
        $this->promise->onResolve(
            static function (?Throwable $throwable, $value) use ($callback): ?Promise {
                $result = $callback($throwable, $value);
                if ($result instanceof Awaitable) {
                    return new PromiseAwaitable($result);
                }

                return null;
            }
        );
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
