<?php

declare(strict_types=1);

namespace Psl\Example\UDP;

use Psl\Async;
use Psl\IO;
use Psl\Network\Address;
use Psl\UDP;

require __DIR__ . '/../../vendor/autoload.php';

Async\concurrently::<string, void>([
    'server' => static function (): void {
        $socket = UDP\Socket::bind('127.0.0.1', 9999);
        IO\write_error_line('< UDP echo server listening on port 9999');

        // Handle 3 messages then stop
        for ($i = 0; $i < 3; $i++) {
            [$data, $address] = $socket->receiveFrom(1024);
            IO\write_error_line('< received from %s: "%s"', $address->toString(), $data);

            // Echo back
            $socket->sendTo($data, $address);
            IO\write_error_line('< echoed back');
        }

        $socket->close();
        IO\write_error_line('< server stopped');
    },
    'client' => static function (): void {
        $socket = UDP\Socket::bind('127.0.0.1', 0);
        IO\write_error_line('> client bound to %s', $socket->getLocalAddress()->toString());

        $serverAddress = Address::udp('127.0.0.1', 9999);

        $messages = ['Hello', 'World', 'UDP!'];
        foreach ($messages as $msg) {
            $socket->sendTo($msg, $serverAddress);
            IO\write_error_line('> sent: "%s"', $msg);

            [$response, $from] = $socket->receiveFrom(1024);
            IO\write_error_line('> received echo: "%s"', $response);
        }

        $socket->close();
        IO\write_error_line('> client done');
    },
]);
