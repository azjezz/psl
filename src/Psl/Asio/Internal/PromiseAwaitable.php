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

    public function onResolve(callable $callback): void
    {
        $this->awaitable->onJoin($callback);
    }
}
