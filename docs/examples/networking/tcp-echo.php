<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

$listener = TCP\listen('127.0.0.1');

Async\concurrently::<string, void>([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $request = $connection->readAll();
        $connection->writeAll("echo: {$request}");
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0);
        $client->writeAll('hello');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
