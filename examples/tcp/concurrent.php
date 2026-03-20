<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

Async\concurrently([
    'server' => static function (): void {
        $listener = TCP\listen('localhost', 91_337);

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

        IO\write_error_line('< server stopped.');
    },
    'client' => static function (): void {
        IO\write_error_line('> client connecting.');

        $client = TCP\connect('localhost', 91_337);

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
