<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$token = new Async\SignalCancellationToken();

// Simulate cancelling after 50ms
Async\Scheduler::delay(Duration::milliseconds(50), static fn() => $token->cancel(
    new RuntimeException('Client disconnected'),
));

$deferred = new Async\Deferred();

try {
    // This will be cancelled before the deferred completes
    $deferred->getAwaitable()->await($token);
} catch (Async\Exception\CancelledException $e) {
    IO\write_line('Cancelled: %s', $e->getPrevious()?->getMessage() ?? 'no cause');
    IO\write_line('Token type: %s', $e->getToken()::class);
}
