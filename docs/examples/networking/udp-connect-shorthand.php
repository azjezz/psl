<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\UDP;

$server = UDP\Socket::bind('127.0.0.1', 0);
$serverAddress = $server->getLocalAddress();

Async\concurrently([
    'server' => static function () use ($server): void {
        [$data, $sender] = $server->receiveFrom(1024);
        $server->sendTo("echo: {$data}", $sender);
        $server->close();
    },
    'client' => static function () use ($serverAddress): void {
        // Create a connected socket directly
        $socket = UDP\connect($serverAddress->host, $serverAddress->port ?? 0);
        $socket->send('hello');
        $_ = $socket->receive(512);
        $socket->close();
    },
]);
