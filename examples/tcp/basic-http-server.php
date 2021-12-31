<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\TCP;
use Throwable;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $server = TCP\Server::create('localhost', 3030);

    IO\write_error_line('Server is listening on http://localhost:3030');

    $watcher = Async\Scheduler::onSignal(SIGINT, $server->close(...));
    Async\Scheduler::unreference($watcher);

    foreach ($server->incoming() as $connection) {
        Async\Scheduler::defer(static function() use ($connection) {
            try {
                $request = $connection->read();

                $connection->writeAll("HTTP/1.1 200 OK\n");
                $connection->writeAll("Server: PHP-Standard-Library TCP Server - https://github.com/azjezz/psl\n");
                $connection->writeAll("Connection: close\n");
                $connection->writeAll("Content-Type: text/plain; charset=utf-8\n\n");
                $connection->writeAll("Hello, World!");
                $connection->close();
            } catch (Throwable) {
                echo 'error';
            }
        });
    }

    IO\write_error_line('');
    IO\write_error_line('Goodbye 👋');

    return 0;
});
