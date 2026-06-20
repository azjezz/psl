<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

// TimeoutCancellationToken auto-cancels after the given duration
$token = new Async\TimeoutCancellationToken(Duration::milliseconds(50));

$deferred = new Async\Deferred::<string>();

// Keep the loop alive
Async\run::<void>(static function () use ($deferred): void {
    Async\sleep(Duration::seconds(5));
    $deferred->complete('too late');
})->ignore();

try {
    $deferred->getAwaitable()->await($token);
} catch (Async\Exception\CancelledException $e) {
    $cause = $e->getPrevious();
    IO\write_line('Timed out: %s', $cause !== null ? $cause::class : 'unknown'); // Psl\Async\Exception\TimeoutException
}
