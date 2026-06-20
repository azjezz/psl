<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Network\Address;
use Psl\UDP;

$server = UDP\Socket::bind('127.0.0.1', 0);
$serverAddress = $server->getLocalAddress();

Async\concurrently::<string, void>([
    'server' => static function () use ($server): void {
        [$data, $sender] = $server->receiveFrom(1024);
        $server->sendTo("echo: {$data}", $sender);
        $server->close();
    },
    'client' => static function () use ($serverAddress): void {
        $client = UDP\Socket::bind('127.0.0.1', 0);
        $client->sendTo('hello', Address::udp($serverAddress->host, $serverAddress->port ?? 0));
        [$response, $_] = $client->receiveFrom(1024);
        $client->close();
    },
]);
