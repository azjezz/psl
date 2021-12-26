<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;
use Psl\Unix;
use const PHP_OS_FAMILY;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    if (PHP_OS_FAMILY === 'Windows') {
        IO\write_error_line('This example requires does not support Windows.');

        return 0;
    }

    $file = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";

    Async\concurrently([
        'server' => static function () use ($file): void {
            $server = Unix\Server::create($file);

            IO\write_error_line('< server is listening.');

            $connection = $server->nextConnection();

            IO\write_error_line('< connection received.');
            IO\write_error_line('< awaiting request.');

            $request = $connection->read();

            IO\write_error_line('< received request: "%s".', $request);
            IO\write_error_line('< sending response.');

            $connection->writeAll(Str\reverse($request));
            $connection->close();

            IO\write_error_line('< connection closed.');

            $server->close();

            IO\write_error_line("< server stopped\n");
        },
        'client' => static function () use ($file): void {
            $client = Unix\connect($file);

            IO\write_error_line('> client connected.');
            IO\write_error_line('> sending request.');

            $client->writeAll('Hello, World!');

            IO\write_error_line('> awaiting response.');

            $response = $client->readAll();

            IO\write_error_line('> received response: "%s".', $response);

            $client->close();

            IO\write_error_line('> client disconnected.');
        },
    ]);

    return 0;
});
