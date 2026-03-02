<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Network\Address;
use Psl\UDP;

$socket = UDP\Socket::bind('127.0.0.1');
$serverAddress = $socket->getLocalAddress();

Async\concurrently([
    'server' => static function () use ($socket): void {
        // Peek at data without consuming it
        [$data, $_sender] = $socket->peekFrom(1024);
        echo "Peeked: {$data}\n";

        // Same data is still available for receiveFrom()
        [$data, $_sender] = $socket->receiveFrom(1024);
        echo "Received: {$data}\n";

        $socket->close();
    },
    'client' => static function () use ($serverAddress): void {
        $client = UDP\Socket::bind('127.0.0.1');
        $client->sendTo('hello', Address::udp($serverAddress->host, $serverAddress->port ?? 0));
        $client->close();
    },
]);
