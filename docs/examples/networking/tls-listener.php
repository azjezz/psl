<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\TCP;
use Psl\TLS;

// Create a TLS listener wrapping a TCP listener
$certificate = new TLS\Certificate('server.pem', 'server.key');
$listener = new TLS\Listener(TCP\listen('127.0.0.1', 0), TLS\ServerConfig::create($certificate));

$address = $listener->getLocalAddress();
IO\write_line('TLS server listening on %s:%d', $address->host, $address->port ?? 0);

// Simulate shutdown after 50ms
$token = new Async\SignalCancellationToken();
Async\run(static function () use ($token): void {
    Async\sleep(Psl\DateTime\Duration::milliseconds(50));
    $token->cancel();
})->ignore();

while (true) {
    try {
        $stream = $listener->accept($token);

        IO\write_line('Accepted TLS connection from %s', $stream->getPeerAddress()->host);

        $stream->close();
    } catch (Async\Exception\CancelledException) {
        break;
    }
}

$listener->close();
