<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl;
use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 *
 * @internal
 */
final class WaitHandle
{
    private bool $failed = false;
    private bool $finished = false;

    /**
     * @var Awaitable<T>|T|null $result
     */
    private $result = null;

    /**
     * @var (
     *        (callable(): void)|
     *        (callable(): Awaitable<void>)|
     *        (callable(): mixed)|
     *        (callable(): Awaitable<mixed>)|
     *        (callable(?Throwable): mixed)|
     *        (callable(?Throwable): Awaitable<mixed>)|
     *        (callable(?Throwable, ?T): mixed)|
     *        (callable(?Throwable, ?T): Awaitable<mixed>)|
     *        (callable(?Throwable): void)|
     *        (callable(?Throwable): Awaitable<void>)|
     *        (callable(?Throwable, ?T): void)|
     *        (callable(?Throwable, ?T): Awaitable<void>)
     *        )|null
     */
    private $onJoin = null;

    /**
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
     * ) $callback
     */
    public function onJoin(callable $callback): void
    {
        if ($this->finished) {
            $result = $this->result;
            if ($result instanceof Awaitable) {
                /** @var Awaitable<T> $result */
                $result->onJoin($callback);
                return;
            }

            EventLoop::defer(static function () use ($callback, $result): void {
                $callback(null, $result);
            }, null);

            return;
        }

        if (null === $this->onJoin) {
            $this->onJoin = $callback;

            return;
        }

        $previous = $this->onJoin;
        $this->onJoin =
            /**
             * @param T|null $value
             */
            static function (?Throwable $exception = null, mixed $value = null) use ($previous, $callback): void {
                foreach ([$previous, $callback] as $fun) {
                    try {
                        /** @var mixed $result */
                        $result = $fun($exception, $value);

                        if ($result instanceof Awaitable) {
                            $result->onJoin(static function (?Throwable $exception): void {
                                if ($exception) {
                                    throw $exception;
                                }
                            });
                        }
                    } catch (Throwable $exception) {
                        EventLoop::defer(static function () use ($exception) {
                            throw $exception;
                        }, null);
                    }
                }
            };
    }

    public function __destruct()
    {
        try {
            $this->result = null;
        } catch (Throwable $exception) {
            EventLoop::defer(static function () use ($exception) {
                throw $exception;
            }, null);
        }
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param T|Awaitable<T> $value
     *
     * @throws Psl\Exception\InvariantViolationException If the wait handle has already been finished.
     */
    public function finish(mixed $value = null): void
    {
        Psl\invariant(!$this->finished, 'Awaitable has already been finished.');

        $this->finished = true;
        $this->result = $value;

        if ($this->onJoin === null) {
            return;
        }

        $onJoin = $this->onJoin;
        $this->onJoin = null;

        $result = $this->result;
        if ($result instanceof Awaitable) {
            /** @var Awaitable<T> $result */
            $result->onJoin($onJoin);

            return;
        }

        EventLoop::defer(static function () use ($onJoin, $result): void {
            $onJoin(null, $result);
        }, null);
    }

    /**
     * @param Throwable $reason Failure reason.
     *
     * @throws Psl\Exception\InvariantViolationException If the wait handle has already been finished.
     */
    public function fail(Throwable $reason): void
    {
        $this->failed = true;
        /** @var Awaitable<T> $handle */
        $handle = new FailedAwaitable($reason);
        $this->finish($handle);
    }

    public function isFailed(): bool
    {
        return $this->failed;
    }
}
