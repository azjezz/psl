<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;
use Psl\Unix;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $file = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";

    $output = IO\output_handle();

    Async\concurrent([
        'server' => static function () use ($file, $output): void {
            $server = Unix\Server::create($file);
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
        'client' => static function () use ($file, $output): void {
            $client = Unix\connect($file);
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
