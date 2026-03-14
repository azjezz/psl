<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$semaphore = new Async\Semaphore(1, static function (string $task): string {
    Async\sleep(Duration::milliseconds(100));

    return $task . ' done';
});

// First task starts immediately
Async\run(static function () use ($semaphore): void {
    $result = $semaphore->waitFor('task-1');
    IO\write_line($result);
})->ignore();

// Second task must wait — but we cancel it after 50ms
$token = new Async\TimeoutCancellationToken(Duration::milliseconds(50));

try {
    $semaphore->waitFor('task-2', $token);
} catch (Async\Exception\CancelledException) {
    IO\write_line('task-2 cancelled while waiting for semaphore slot');
}
