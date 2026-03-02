<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$deferred = new Async\Deferred();

// Schedule a timeout after 1 second
$timeout = Async\Scheduler::delay(Duration::seconds(1), static function () use ($deferred): void {
    $deferred->error(new Async\Exception\TimeoutException('Task timed out'));
});

$awaitable = Async\run(static function () use ($deferred, $timeout): void {
    Async\sleep(Duration::seconds(4));
    Async\Scheduler::cancel($timeout);
    $deferred->complete(null);
});

try {
    $deferred->getAwaitable()->await();
} catch (Async\Exception\TimeoutException) {
    IO\write_line('Task timed out as expected');
}
