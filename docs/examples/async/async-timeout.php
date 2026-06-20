<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$cancellation = new Async\TimeoutCancellationToken(Duration::seconds(1));

$awaitable = Async\run::<void>(static function () use ($cancellation): void {
    // Simulate a long-running task
    Async\sleep(Duration::seconds(4));

    // Check if the operation was cancelled
    $cancellation->throwIfCancelled();
});

try {
    $awaitable->await();
} catch (Async\Exception\CancelledException) {
    IO\write_line('Task timed out as expected');
}
