<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

// Simulate a request-scoped token that cancels when the client disconnects
$requestToken = new Async\SignalCancellationToken();

// Combine with a per-operation timeout
$linked = new Async\LinkedCancellationToken(
    $requestToken,
    new Async\TimeoutCancellationToken(Duration::milliseconds(50)),
);

$deferred = new Async\Deferred::<string>();

Async\run::<void>(static function () use ($deferred): void {
    Async\sleep(Duration::seconds(5));
    $deferred->complete('too late');
})->ignore();

try {
    // Cancelled by whichever fires first
    $deferred->getAwaitable()->await($linked);
} catch (Async\Exception\CancelledException $e) {
    $cause = $e->getToken();
    IO\write_line('Cancelled by: %s', $cause::class);
}
