<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\TCP;

// Demonstrate StreamInterface usage with a TCP server and client
$listener = TCP\listen('127.0.0.1');

Async\concurrently([
    'server' => static function () use ($listener): void {
        $stream = $listener->accept();

        // Read and write data
        $stream->writeAll('hello');
        $stream->shutdown(); // signal EOF to the remote peer

        $_response = $stream->readAll();

        // Peek at incoming data without consuming it
        // (only works before data is consumed)

        // Inspect addresses
        $_local = $stream->getLocalAddress();
        $_remote = $stream->getPeerAddress();

        $stream->close();
        $listener->close();
    },
    'client' => static function () use ($listener): void {
        $address = $listener->getLocalAddress();
        $client = TCP\connect($address->host, $address->port ?? 0);

        $data = $client->readAll();
        $client->writeAll('reply');
        $client->close();
    },
]);
