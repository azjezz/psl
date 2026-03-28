<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

// Connect from a specific local address.
// The bindTo option binds the socket to a local IP before connecting,
// useful for selecting a particular network interface or source IP.
$listener = TCP\listen('127.0.0.1');
$port = $listener->getLocalAddress()->port ?? 0;

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $connection->writeAll('hello');
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($port): void {
        $stream = TCP\connect('127.0.0.1', $port, new TCP\ConnectConfiguration(bindTo: '127.0.0.1:0'));

        $local = $stream->getLocalAddress();
        Psl\invariant($local->host === '127.0.0.1', 'Host address is wrong');

        $data = $stream->readAll();
        $stream->close();
    },
]);
