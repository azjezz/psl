<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

/**
 * @return Async\Awaitable<'hello'>
 */
function get_message(): Async\Awaitable
{
    /** @var Async\Deferred<'hello'> $deferred */
    $deferred = new Async\Deferred();

    // Complete the deferred with 'hello' after 2 seconds.
    Async\Scheduler::delay(Duration::seconds(2), static fn() => $deferred->complete('hello'));

    return $deferred->getAwaitable();
}

get_message()->await(); // 'hello'
