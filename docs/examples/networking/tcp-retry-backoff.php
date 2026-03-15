<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\TCP;

// Create a retry connector with exponential backoff
$connector = new TCP\RetryConnector(
    new TCP\Connector(new TCP\ConnectConfiguration(noDelay: true)),
    maxAttempts: 5,
    backoff: Duration::milliseconds(500),
);

// Demonstrate by connecting to a local server
$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $connection->writeAll('connected');
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($connector, $listener): void {
        $address = $listener->getLocalAddress();
        $stream = $connector->connect($address->host, $address->port ?? 0);
        $data = $stream->readAll();
        $stream->close();
    },
]);
