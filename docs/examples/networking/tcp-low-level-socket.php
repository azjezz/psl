<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP\Socket;

$socket = Socket::createV4();
$socket->setReuseAddress(true);
$socket->setReusePort(true);
$socket->setNoDelay(true);
$socket->bind('127.0.0.1', 0);

$listener = $socket->listen();

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $data = $connection->readAll();
        $connection->writeAll($data);
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = \Psl\TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('test');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
