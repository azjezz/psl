<?php

declare(strict_types=1);

namespace Psl\Asio;

use Fiber;
use Throwable;

/**
 * @template T
 *
 * @param (callable(): T) $fun
 */
function defer(callable $fun): void
{
    $fiber = new Fiber(static function () use ($fun): void {
        try {
            $fun();
        } catch (Throwable $exception) {
            Internal\EventLoop::defer(static function () use ($exception) {
                throw $exception;
            }, null);
        }
    });

    Internal\EventLoop::defer(
        static function () use ($fiber): void {
            $fiber->start();
        },
        null
    );
}
