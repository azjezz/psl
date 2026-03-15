<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl\Default\DefaultInterface;

/**
 * A no-op cancellation token that is never cancelled.
 *
 * Used as the default parameter value for operations that accept a cancellation token.
 */
final readonly class NullCancellationToken implements CancellationTokenInterface, DefaultInterface
{
    public bool $cancellable;

    public function __construct()
    {
        $this->cancellable = false;
    }

    public static function default(): static
    {
        return new self();
    }

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
