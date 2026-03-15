<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $_ = $connection->readAll();
        $connection->writeAll("HTTP/1.0 200 OK\r\n\r\nHello");
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect(
            $address->host,
            $address->port ?? 0,
            cancellation: new Async\TimeoutCancellationToken(Duration::seconds(5)),
        );
        $client->writeAll("GET / HTTP/1.0\r\nHost: localhost\r\n\r\n");
        $client->shutdown();
        $_ = $client->readAll();
        $client->close();
    },
]);
