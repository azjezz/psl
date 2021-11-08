<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): never {
    $server = TCP\Server::create('localhost');

    IO\output_handle()->writeAll("Server is listening on http://localhost:" . $server->getLocalAddress()->port . "\n");

    while (true) {
        $connection = $server->nextConnection();
        $request = $connection->read();

        IO\output_handle()->writeAll(Str\split($request, "\n")[0] . "\n");

        $connection->writeAll("HTTP/1.1 200 OK\n");
        $connection->writeAll("Server: PSL\n");
        $connection->writeAll("Connection: close\n");
        $connection->writeAll("Content-Type: text/plain\n\n");

        $connection->writeAll("Hello, World!");

        $connection->close();
    }
});
