<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Reschedule the work of an async function until some other time in the future.
 *
 * The common use case for this is if your async function actually has to wait for some blocking call,
 * you can tell other Awaitables in the async scheduler that they can work while this one waits for
 * the blocking call to finish (e.g., maybe in a polling situation or something).
 */
function later(): void
{
    $suspension = Scheduler::createSuspension();

    Scheduler::defer(static fn () => $suspension->resume());

    $suspension->suspend();
}
