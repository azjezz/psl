<?php

declare(strict_types=1);

namespace Psl\Asio;

/**
 * Wait Handle.
 *
 * @template T
 */
interface Awaitable
{
    /**
     * Check if this wait handle succeeded.
     */
    public function isSucceeded(): bool;

    /**
     * Check if this wait handle finished (succeeded or failed).
     */
    public function isFinished(): bool;

    /**
     * Check if this wait handle failed.
     */
    public function isFailed(): bool;

    /**
     * Registers a callback to be invoked when the awaitable is finished.
     *
     * Note that using this method directly is generally not recommended.
     *
     * Use the {@see await()} function to await handle resolution.
     *
     * If this method is called multiple times, additional handlers will be registered instead
     * of replacing any already existing handlers.
     *
     * Registered callbacks MUST be invoked asynchronously when the awaitable is finished using
     * a defer watcher in the event-loop.
     *
     * Note: You shouldn't implement this interface yourself. Instead, provide a method that returns
     * an awaitable for the operation you're implementing.
     *
     * @param (
     *  (callable(): void)|
     *  (callable(): Awaitable<void>)|
     *  (callable(): mixed)|
     *  (callable(): Awaitable<mixed>)|
     *  (callable(?Throwable): mixed)|
     *  (callable(?Throwable): Awaitable<mixed>)|
     *  (callable(?Throwable, ?T): mixed)|
     *  (callable(?Throwable, ?T): Awaitable<mixed>)|
     *  (callable(?Throwable): void)|
     *  (callable(?Throwable): Awaitable<void>)|
     *  (callable(?Throwable, ?T): void)|
     *  (callable(?Throwable, ?T): Awaitable<void>)
     * ) $callable
     *
     * @internal
     */
    public function onJoin(callable $callable): void;
}
