<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Fiber;
use Psl\Exception;

use function register_shutdown_function;

/**
 * Accessor to allow global access to the event loop.
 *
 * @see Driver
 */
final class EventLoop
{
    private static ?Driver\DriverInterface $driver = null;

    private static ?Fiber $fiber = null;

    public static function driver(): Driver\DriverInterface
    {
        if (null === self::$driver) {
            self::$driver = new Driver\NativeDriver();
        }

        return self::$driver;
    }

    /**
     * {@see Driver\DriverInterface::defer()}.
     *
     * @template T
     *
     * @param callable(string, T) $callback
     * @param T $data
     */
    public static function defer(callable $callback, $data): string
    {
        return self::driver()->defer($callback, $data);
    }

    /**
     * {@see Driver\DriverInterface::delay()}.
     *
     * @template T
     *
     * @param (callable(string, T): void) $callback
     * @param T $data
     */
    public static function delay(int $delay, callable $callback, $data): string
    {
        return self::driver()->delay($delay, $callback, $data);
    }

    /**
     * {@see Driver\DriverInterface::repeat()}.
     *
     * @template T
     *
     * @param (callable(string, T): void) $callback
     * @param T $data
     */
    public static function repeat(int $interval, callable $callback, $data): string
    {
        return self::driver()->repeat($interval, $callback, $data);
    }

    /**
     * {@see Driver\DriverInterface::onReadable()}.
     *
     * @template T
     *
     * @param resource $stream
     * @param callable(string, resource, T) $callback
     * @param T $data
     */
    public static function onReadable($stream, callable $callback, $data): string
    {
        return self::driver()->onReadable($stream, $callback, $data);
    }

    /**
     * {@see Driver\DriverInterface::onWritable()}.
     *
     * @template T
     *
     * @param resource $stream
     * @param callable(string, resource, T) $callback
     * @param T $data
     */
    public static function onWritable($stream, callable $callback, $data = null): string
    {
        return self::driver()->onWritable($stream, $callback, $data);
    }

    /**
     * {@see Driver\DriverInterface::enable()}.
     */
    public static function enable(string $watcherId): void
    {
        self::driver()->enable($watcherId);
    }

    /**
     * {@see Driver\DriverInterface::disable()}.
     */
    public static function disable(string $watcherId): void
    {
        self::driver()->disable($watcherId);
    }

    /**
     * {@see Driver\DriverInterface::cancel()}.
     */
    public static function cancel(string $watcherId): void
    {
        self::driver()->cancel($watcherId);
    }

    /**
     * {@see Driver\DriverInterface::reference()}.
     *
     * @throws Exception\InvariantViolationException If the watcher identifier is invalid.
     */
    public static function reference(string $watcherId): void
    {
        self::driver()->reference($watcherId);
    }

    /**
     * {@see Driver\DriverInterface::unreference()}.
     */
    public static function unreference(string $watcherId): void
    {
        self::driver()->unreference($watcherId);
    }

    /**
     * {@see Driver\DriverInterface::now()}.
     */
    public static function now(): int
    {
        return self::driver()->now();
    }

    /**
     * {@see Driver\DriverInterface::setState()}.
     *
     * @param mixed $value
     */
    public static function setState(string $key, $value): void
    {
        self::driver()->setState($key, $value);
    }

    /**
     * {@see Driver\DriverInterface::getState()}.
     *
     * @return mixed
     */
    public static function getState(string $key)
    {
        return self::driver()->getState($key);
    }

    /**
     * {@see Driver\DriverInterface::setErrorHandler()}.
     *
     * @param callable(\Throwable):void|null $callback
     *
     * @return callable(\Throwable):void|null
     */
    public static function setErrorHandler(callable $callback = null): ?callable
    {
        return self::driver()->setErrorHandler($callback);
    }

    /**
     * Retrieve the fiber instance associated with the event loop driver.
     */
    public static function getFiber(): Fiber
    {
        $fiber = self::$fiber;
        if (null === $fiber || $fiber->isTerminated()) {
            $fiber = new Fiber(static fn () => self::driver()->run());
            self::$fiber = $fiber;
            // Run event loop to completion on shutdown.
            register_shutdown_function(static function () use ($fiber): void {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                }
            });
        }

        return $fiber;
    }
}
