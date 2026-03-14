<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\TLS;

// Cancel if the TLS handshake takes more than 5 seconds
$token = new Async\TimeoutCancellationToken(Duration::seconds(5));

try {
    $stream = TLS\connect('example.com', 443, cancellation: $token);

    IO\write_line('Connected!');

    $stream->close();
} catch (Async\Exception\CancelledException) {
    IO\write_line('TLS handshake timed out');
}
