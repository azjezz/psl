<?php

declare(strict_types=1);

namespace Psl\Async;

use Generator;
use Psl\Async\Internal\AwaitableIterator;
use Psl\Async\Internal\State;
use Throwable;

use function is_array;

/**
 * @template T
 */
final class Awaitable
{
    private State $state;

    /**
     * @param State<T> $state
     *
     * @internal Use {@see Deferred} to create and resolve an awaitable.
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Iterate over the given awaitables in completion order.
     *
     * @template Tk
     * @template Tv
     *
     * @param iterable<Tk, Awaitable<Tv>> $awaitables
     *
     * @return Generator<Tk, Awaitable<Tv>, void, void>
     */
    public static function iterate(iterable $awaitables): Generator
    {
        $iterator = new AwaitableIterator();

        if (is_array($awaitables)) {
            foreach ($awaitables as $key => $awaitable) {
                /** @psalm-suppress MissingThrowsDocblock */
                $iterator->enqueue($awaitable->state, $key, $awaitable);
            }

            /** @psalm-suppress MissingThrowsDocblock */
            $iterator->complete();
        } else {
            Scheduler::defer(static function () use ($awaitables, $iterator): void {
                // @codeCoverageIgnoreStart
                try {
                    foreach ($awaitables as $key => $awaitable) {
                        $iterator->enqueue($awaitable->state, $key, $awaitable);
                    }

                    /** @psalm-suppress MissingThrowsDocblock */
                    $iterator->complete();
                } catch (Throwable $exception) {
                    /** @psalm-suppress MissingThrowsDocblock */
                    $iterator->error($exception);
                }
                // @codeCoverageIgnoreEnd
            });
        }

        /** @psalm-suppress MissingThrowsDocblock */
        while ($item = $iterator->consume()) {
            yield $item[0] => $item[1];
        }
    }

    /**
     * @template Tv
     *
     * @param Tv $result
     *
     * @return Awaitable<Tv>
     */
    public static function complete(mixed $result): self
    {
        /** @var State<Tv> $state */
        $state = new State();
        /** @psalm-suppress MissingThrowsDocblock */
        $state->complete($result);

        return new self($state);
    }

    /**
     * @return Awaitable<void>
     */
    public static function error(Throwable $throwable): self
    {
        /** @var State<void> $state */
        $state = new State();
        /** @psalm-suppress MissingThrowsDocblock */
        $state->error($throwable);

        return new self($state);
    }

    /**
     * @return bool True if the operation has completed.
     */
    public function isComplete(): bool
    {
        return $this->state->isComplete();
    }

    /**
     * Attaches a callback that is invoked if this awaitable completes.
     *
     * The returned awaitable is completed with the return value of the callback,
     * or errors with an exception thrown from the callback.
     *
     * @template Ts
     *
     * @param callable(T): Ts $on_success
     * @param callable(Throwable): Ts $on_failure
     *
     * @return Awaitable<Ts>
     */
    public function then(callable $on_success, callable $on_failure): self
    {
        /** @var State<Ts> $state */
        $state = new State();

        $this->state->subscribe(
            /**
             * @param null|Throwable $error
             * @param null|T $value
             */
            static function (?Throwable $error, mixed $value) use ($state, $on_success, $on_failure): void {
                if ($error) {
                    try {
                        $state->complete($on_failure($error));
                    } catch (Throwable $throwable) {
                        $state->error($throwable);
                    }

                    return;
                }

                try {
                    /**
                     * @var T $value
                     */
                    $state->complete($on_success($value));
                } catch (Throwable $exception) {
                    $state->error($exception);
                }
            },
        );

        return new self($state);
    }


    /**
     * Awaits the operation to complete.
     *
     * Throws an exception if the operation fails.
     *
     * @return T
     */
    public function await(): mixed
    {
        $suspension = Scheduler::createSuspension();

        $this->state->subscribe(
            /**
             * @param null|Throwable $error
             * @param null|T $value
             */
            static function (?Throwable $error, mixed $value) use ($suspension): void {
                if ($error) {
                    $suspension->throw($error);
                } else {
                    $suspension->resume($value);
                }
            },
        );

        /** @var T */
        return $suspension->suspend();
    }

    /**
     * Do not forward unhandled errors to the event loop handler.
     */
    public function ignore(): void
    {
        $this->state->ignore();
    }
}
