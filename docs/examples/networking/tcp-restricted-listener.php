<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\CIDR;
use Psl\IO;
use Psl\IP;
use Psl\TCP;

// Only allow connections from localhost and the 10.0.0.0/8 private range
$inner = TCP\listen('127.0.0.1', 0);
$listener = new TCP\RestrictedListener($inner, [
    IP\Address::parse('127.0.0.1'),
    new CIDR\Block('10.0.0.0/8'),
]);

$address = $listener->getLocalAddress();
IO\write_line('Restricted listener on %s:%d', $address->host, $address->port ?? 0);

$token = new Async\SignalCancellationToken();
Async\Scheduler::delay(Psl\DateTime\Duration::milliseconds(50), static fn() => (
    // Simulate shutdown after 50ms
    $token->cancel()
));

while (true) {
    try {
        $stream = $listener->accept($token);

        IO\write_line('Accepted from %s', $stream->getPeerAddress()->host);

        $stream->close();
    } catch (Async\Exception\CancelledException) {
        break;
    }
}

$listener->close();
