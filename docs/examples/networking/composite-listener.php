<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\Network;
use Psl\TCP;

// Listen on two different ports simultaneously
$listener1 = TCP\listen('127.0.0.1', 0);
$listener2 = TCP\listen('127.0.0.1', 0);

$composite = new Network\CompositeListener([$listener1, $listener2]);

$addr1 = $listener1->getLocalAddress();
$addr2 = $listener2->getLocalAddress();
IO\write_line('Listening on %s:%d and %s:%d', $addr1->host, $addr1->port ?? 0, $addr2->host, $addr2->port ?? 0);

// Simulate shutdown after 50ms
$token = new Async\SignalCancellationToken();
Async\Scheduler::delay(Psl\DateTime\Duration::milliseconds(50), static fn(string $_) => $token->cancel());

while (true) {
    try {
        $stream = $composite->accept($token);

        IO\write_line('Connection from %s', $stream->getPeerAddress()->host);

        $stream->close();
    } catch (Async\Exception\CancelledException) {
        break;
    }
}

// Closes all inner listeners
$composite->close();
