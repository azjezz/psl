<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl;
use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 */
final class Deferred
{
    /**
     * @var WaitHandle<T>
     */
    private WaitHandle $handle;

    /**
     * @var InternalAwaitable<T>
     */
    private InternalAwaitable $awaitable;

    public function __construct()
    {
        $this->handle = new WaitHandle();
        $this->awaitable = new InternalAwaitable($this->handle);
    }

    /**
     * @return Awaitable<T>
     */
    public function awaitable(): Awaitable
    {
        return $this->awaitable;
    }

    /**
     * @return bool True if the contained awaitable has finished.
     */
    public function isFinished(): bool
    {
        return $this->handle->isFinished();
    }

    /**
     * Finish the awaitable handle with the given value.
     *
     * @param T|Awaitable<T> $value
     *
     * @throws Psl\Exception\InvariantViolationException If the wait handle has already been finished.
     */
    public function finish(mixed $value = null): void
    {
        $this->handle->finish($value);
    }

    /**
     * Fails the awaitable using the given exception.
     *
     * @throws Psl\Exception\InvariantViolationException If the wait handle has already been finished.
     */
    public function fail(Throwable $reason): void
    {
        $this->handle->fail($reason);
    }
}
