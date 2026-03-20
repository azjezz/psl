<?php

declare(strict_types=1);

namespace Psl\Example\Unix;

use Psl\Async;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;
use Psl\Unix;

use const PHP_OS_FAMILY;

require __DIR__ . '/../../vendor/autoload.php';

if (PHP_OS_FAMILY === 'Windows') {
    IO\write_error_line('This example does not support Windows.');

    return 0;
}

$file = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';

Async\concurrently([
    'server' => static function () use ($file): void {
        $listener = Unix\listen($file);

        IO\write_error_line('< server is listening.');

        $connection = $listener->accept();

        IO\write_error_line('< connection received.');
        IO\write_error_line('< awaiting request.');

        $request = $connection->read();

        IO\write_error_line('< received request: "%s".', $request);
        IO\write_error_line('< sending response.');

        $connection->writeAll(Str\reverse($request));
        $connection->close();

        IO\write_error_line('< connection closed.');

        $listener->close();

        IO\write_error_line("< server stopped\n");
    },
    'client' => static function () use ($file): void {
        IO\write_error_line('> client connecting.');

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
