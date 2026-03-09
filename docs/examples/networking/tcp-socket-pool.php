<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');
$address = $listener->getLocalAddress();

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        // Keep connection open until client is done
        $connection->readAll();
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($address): void {
        $pool = new TCP\SocketPool();
        $stream = $pool->checkout($address->host, $address->port ?? 0);
        // ... use stream ...
        $pool->checkin($stream);

        // Later -- reuses the same connection
        $stream = $pool->checkout($address->host, $address->port ?? 0);
        $pool->clear($stream);
        $pool->close();
    },
]);
