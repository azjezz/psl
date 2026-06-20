<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently::<string, void>([
    'server' => static function () use ($listener): void {
        echo "Listening on {$listener->getLocalAddress()->toString()}\n";

        // Accept one connection then shut down
        $connection = $listener->accept();
        Async\run::<void>(static function () use ($connection): void {
            $data = $connection->readAll();
            $connection->writeAll($data);
            $connection->close();
        })->await();

        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('hello from client');
        $client->shutdown();
        $response = $client->readAll();
        echo "Got: {$response}\n";
        $client->close();
    },
]);
