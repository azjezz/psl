<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Amp\Promise;
use Psl\Asio\Awaitable;

/**
             * @template T
 *
 * @template-implements Promise<T>
 *
 * @internal
 */
final class PromiseAwaitable implements Promise
{
    private Awaitable $awaitable;

    /**
     * @param Awaitable<T> $awaitable
     */
    public function __construct(
        Awaitable $awaitable
    ) {
        $this->awaitable = $awaitable;
    }

    /**
     * @psalm-param (callable(\Throwable|null, mixed):Promise|null) $onResolved
     * 
     * @psalm-suppress MismatchingDocblockParamType,PossiblyNullFunctionCall
     */
    public function onResolve(callable $onResolved): void
    {
        $this->awaitable->onJoin(
            /**
             * @param T|null $value
             *
             * @return Awaitable<mixed>
             */
            static function (?\Throwable $throwable, $value) use ($onResolved): Awaitable {
                $result = $onResolved($throwable, $value);
                if ($result instanceof Promise) {
                    return new AwaitablePromise($result);
                }

                return new FinishedAwaitable();
            }
        );
    }
}
