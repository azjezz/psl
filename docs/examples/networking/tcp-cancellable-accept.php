<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1', 0);
$token = new Async\SignalCancellationToken();

// Simulate shutdown after 50ms
Async\run(static function () use ($token): void {
    Async\sleep(Psl\DateTime\Duration::milliseconds(50));
    $token->cancel();
})->ignore();

while (true) {
    try {
        $conn = $listener->accept($token);
    } catch (Async\Exception\CancelledException) {
        IO\write_line('Graceful shutdown');
        break;
    }
}

$listener->close();
