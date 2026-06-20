<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

// DEPRECATED: TCP\Socket is deprecated. Use the bindTo option instead.
//
// Before (deprecated):
//   $socket = TCP\Socket::createV4();
//   $socket->bind('127.0.0.1', 0);
//   $stream = $socket->connect('example.com', 443);
//
// After:
//   $stream = TCP\connect('example.com', 443, new TCP\ConnectConfiguration(
//       bindTo: '127.0.0.1:0',
//   ));

// The new way: use bindTo on ConnectConfiguration or ListenConfiguration.
$listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(reuseAddress: true, noDelay: true));

Async\concurrently::<string, void>([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $data = $connection->readAll();
        $connection->writeAll($data);
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0, new TCP\ConnectConfiguration(bindTo: '127.0.0.1:0'));
        $client->writeAll('test');
        $client->shutdown();
        $response = $client->readAll();
        $client->close();
    },
]);
