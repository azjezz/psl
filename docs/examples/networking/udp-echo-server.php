<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Network\Address;
use Psl\UDP;

$socket = UDP\Socket::bind('127.0.0.1', 0);
$serverAddress = $socket->getLocalAddress();
echo "Listening on {$serverAddress->toString()}\n";

Async\concurrently([
    'server' => static function () use ($socket): void {
        // Echo one datagram then shut down
        [$data, $sender] = $socket->receiveFrom(65_507);
        $socket->sendTo($data, $sender);
        $socket->close();
    },
    'client' => static function () use ($serverAddress): void {
        $client = UDP\Socket::bind('127.0.0.1');
        $client->sendTo('ping', Address::udp($serverAddress->host, $serverAddress->port ?? 0));
        [$response, $_] = $client->receiveFrom(1024);
        echo "Got: {$response}\n";
        $client->close();
    },
]);
