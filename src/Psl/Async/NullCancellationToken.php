<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

/**
 * A no-op cancellation token that is never cancelled.
 *
 * Used as the default parameter value for operations that accept a cancellation token.
 */
final class NullCancellationToken implements CancellationTokenInterface
{
    /**
     * @inheritDoc
     */
    public function subscribe(Closure $callback): string
    {
        return 'null';
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(string $id): void {}

    /**
     * @inheritDoc
     */
    public function isCancelled(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function throwIfCancelled(): void {}
}
