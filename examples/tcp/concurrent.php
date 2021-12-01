<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $output = IO\output_handle();

    Async\parallel([
        'server' => static function () use ($output): void {
            $server = TCP\Server::create('localhost', 91337);
            $output->writeAll("< server is listening\n");
            $connection = $server->nextConnection();
            $output->writeAll("< connection received\n");
            $output->writeAll("> awaiting request\n");
            $request = $connection->read();
            $output->writeAll("< received request: $request\n");
            $output->writeAll("< sending response\n");
            $connection->writeAll(Str\reverse($request));
            $connection->close();
            $output->writeAll("< connection closed\n");
            $server->stopListening();
            $output->writeAll("< server stopped\n");
        },
        'client' => static function () use ($output): void {
            $client = TCP\connect('localhost', 91337);
            $output->writeAll("> client connected\n");
            $output->writeAll("> sending request\n");
            $client->writeAll('Hello, World!');
            $output->writeAll("> awaiting response\n");
            $response = $client->readAll();
            $output->writeAll("> received response: $response\n");
            $client->close();
            $output->writeAll("> client disconnected\n");
        },
    ]);

    return 0;
});
