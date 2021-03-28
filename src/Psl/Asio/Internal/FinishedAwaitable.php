<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Amp;
use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 *
 * @template-implements Awaitable<T>
 *
 * @internal
 */
final class FinishedAwaitable implements Awaitable
{
    /**
     * @var T
     */
    private $value;

    /**
     * @param T $value
     */
    public function __construct(
        $value = null
    ) {
        $this->value = $value;
    }

    /**
     * Catches any destructor exception thrown and rethrows it to the event loop.
     */
    public function __destruct()
    {
        try {
            $this->value = null;
        } catch (Throwable $e) {
            Amp\Loop::defer(
                static function () use ($e) {
                    throw $e;
                },
                null
            );
        }
    }

    public function onJoin(callable $onJoin): void
    {
        Amp\Loop::defer(
            fn () => $onJoin(null, $this->value),
            null
        );
    }

    public function isSucceeded(): bool
    {
        return true;
    }

    public function isFinished(): bool
    {
        return true;
    }

    public function isFailed(): bool
    {
        return false;
    }
}
