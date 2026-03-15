<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$token = new Async\SignalCancellationToken();

// Cancel the sleep after 50ms
Async\Scheduler::delay(Duration::milliseconds(50), static fn(string $_) => $token->cancel());

try {
    // Sleep for 5 seconds, but wake early if the token is cancelled
    Async\sleep(Duration::seconds(5), $token);
} catch (Async\Exception\CancelledException) {
    IO\write_line('Woke early!');
}
