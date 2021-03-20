<?php

declare(strict_types=1);

namespace Psl\Asio;

use Error;
use Fiber;
use Psl\Exception;
use Throwable;

/**
 * Wait for the given wait handle to finish.
 *
 * @template T
 *
 * @param Awaitable<T> $awaitable
 *
 * @throws Exception\RuntimeException If an internal error occurred.
 *
 * @return T
 */
function await(Awaitable $awaitable)
{
    /** @var Fiber */
    $fiber = Fiber::this();
    $resolved = false;

    if ($fiber) { // Awaiting from within a fiber.
        if ($fiber === Internal\EventLoop::getFiber()) {
            throw new Exception\RuntimeException('cannot call await() within an event loop callback');
        }

        $awaitable->onJoin(static function (?Throwable $exception, $value) use (&$resolved, $fiber): void {
            $resolved = true;

            if ($exception) {
                $fiber->throw($exception);
                return;
            }

            $fiber->resume($value);
        });

        try {
            // Suspend the current fiber until the awaitable is finished.
            /** @var T */
            $value = Fiber::suspend();
        } finally {
            if (!$resolved) {
                // $resolved should only be false if the fiber was manually resumed outside of the callback above.
                throw new Error('Fiber resumed before awaitable was finished.');
            }
        }

        return $value;
    }

    // Awaiting from {main}.
    $fiber = Internal\EventLoop::getFiber();

    $awaitable->onJoin(static function (?Throwable $exception, $value) use (&$resolved): void {
        $resolved = true;

        // Suspend event loop fiber to {main}.
        if ($exception) {
            Fiber::suspend(static function () use ($exception) {
                throw $exception;
            });
            return;
        }

        Fiber::suspend(static fn () => $value);
    });

    try {
        /** @var (callable(): T) $lambda */
        $lambda = $fiber->isStarted() ? $fiber->resume() : $fiber->start();
    } catch (Throwable $exception) {
        throw new Exception\RuntimeException('Fiber resumed before awaitable was finished.');
    }

    if (!$resolved) {
        // $resolved should only be false if the event loop exited without finishing the awaitable.
        throw new Exception\RuntimeException('Event loop suspended or exited without finishing the awaitable.');
    }

    return $lambda();
}
