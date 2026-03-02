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
        // Start with an unconnected socket
        $socket = UDP\Socket::bind('127.0.0.1', 0);

        // Connect returns a ConnectedSocket -- the original socket is closed
        $connected = $socket->connect($serverAddress->host, $serverAddress->port ?? 0);

        // $socket is now closed -- only $connected is usable
        $connected->send('hello');
        $_response = $connected->receive(512);
        $connected->close();
    },
]);
