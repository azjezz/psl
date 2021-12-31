<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Network;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $server = TCP\Server::create('localhost', 3030);
    $semaphore = new Async\Semaphore(100, static function(Network\SocketInterface $connection): void {
        $request = $connection->read();
        $connection->writeAll("HTTP/1.1 200 OK\n");
        $connection->writeAll("Server: PHP-Standard-Library TCP Server - https://github.com/azjezz/psl\n");
        $connection->writeAll("Connection: close\n");
        $connection->writeAll("Content-Type: text/plain; charset=utf-8\n\n");
        $connection->writeAll("Hello, World!");
        $connection->close();
    });

    IO\write_error_line('Server is listening on http://localhost:3030');

    Async\Scheduler::unreference(
        Async\Scheduler::onSignal(SIGINT, $server->close(...))
    );

    foreach ($server->incoming() as $connection) {
        Async\Scheduler::defer(static fn() => $semaphore->waitFor($connection));

        $semaphore->waitForRoom();
    }

    IO\write_error_line('');
    IO\write_error_line('Goodbye 👋');

    return 0;
});
