<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Network\Exception\AlreadyStoppedException;
use Psl\TCP;
use Throwable;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $server = TCP\Server::create('localhost', 3030);

    IO\output_handle()->writeAll("Server is listening on http://localhost:3030\n");

    Async\Scheduler::defer(static function () use ($server) {
        Async\await_signal(SIGINT);
        $server->stopListening();
    });

    try {
        while (true) {
            $connection = $server->nextConnection();

            Async\Scheduler::defer(static function() use ($connection) {
                try {
                    $stream = $connection->getStream();

                    Async\await_readable($stream);
                    $connection->read();

                    $connection->writeAll("HTTP/1.1 200 OK\n");
                    $connection->writeAll("Server: PHP+PSL\n");
                    $connection->writeAll("Connection: close\n");
                    $connection->writeAll("Content-Type: text/plain\n\n");
                    $connection->writeAll("Hello, World!");
                    $connection->close();
                } catch (Throwable) {
                }
            });
        }
    } catch (AlreadyStoppedException) {
        IO\output_handle()->writeAll("\nGoodbye 👋\n");
    }

    return 0;
});
