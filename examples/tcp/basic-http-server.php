<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Network\Exception\AlreadyStoppedException;
use Psl\Str;
use Psl\TCP;
use Throwable;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $output = IO\output_handle();
    $server = TCP\Server::create('localhost', 3030);

    $output->writeAll("Server is listening on http://localhost:3030\n");

    Async\Scheduler::defer(static function () use ($server, $output) {
        Async\await_signal(SIGINT);

        $output->writeAll("\nGoodbye 👋\n");

        $server->stopListening();
    });

    try {
        while (true) {
            $connection = $server->nextConnection();

            Async\Scheduler::defer(static function() use ($connection, $output) {
                try {
                    $request = $connection->read();

                    $output->writeAll("[" . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . "MiB] " . Str\split($request, "\n")[0] . "\n");

                    $connection->writeAll("HTTP/1.1 200 OK\n");

                    $connection->writeAll("Server: PSL\n");
                    $connection->writeAll("Connection: close\n");
                    $connection->writeAll("Content-Type: text/plain\n\n");

                    $connection->writeAll("Hello, World!");
                    $connection->close();
                } catch (Throwable) {
                }
            });
        }
    } catch (AlreadyStoppedException) {
        // server stopped.
    }

    return 0;
});
