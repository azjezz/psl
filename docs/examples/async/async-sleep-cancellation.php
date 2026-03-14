<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$token = new Async\SignalCancellationToken();

// Cancel the sleep after 50ms from another fiber
Async\run(static function () use ($token): void {
    Async\sleep(Duration::milliseconds(50));
    $token->cancel();
})->ignore();

try {
    // Sleep for 5 seconds, but wake early if the token is cancelled
    Async\sleep(Duration::seconds(5), $token);
} catch (Async\Exception\CancelledException) {
    IO\write_line('Woke early!');
}
