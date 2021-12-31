<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\Html;
use Psl\IO;
use Psl\Network;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

const CONCURRENCY_LIMIT = 50;
const RESPONSE_FORMAT = <<<HTML
<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>PHP Standard Library - TCP server</title>
    </head>
    <body>
        <h1>Hello, World!</h1>
        <pre><code>%s</code></pre>
    </body>
</html>
HTML;

Async\main(static function (): int {
    $server = TCP\Server::create('localhost', 3030);
    $semaphore = new Async\Semaphore(CONCURRENCY_LIMIT, static function (Network\SocketInterface $connection): void {
        try {
            $request = $connection->read();
            $connection->writeAll("HTTP/1.1 200 OK\nConnection: close\nContent-Type: text/html; charset=utf-8\n\n");
            $connection->writeAll(Str\format(RESPONSE_FORMAT, Html\encode_special_characters($request)));
            $connection->close();
        } catch (IO\Exception\RuntimeException $exception) {
            IO\write_error_line('Error: %s', $exception->getMessage());
        }
    });

    Async\Scheduler::unreference(Async\Scheduler::onSignal(SIGINT, $server->close(...)));

    IO\write_error_line('Server is listening on http://localhost:3030');
    IO\write_error_line('Click Ctrl+C to stop the server.');

    foreach ($server->incoming() as $connection) {
        Async\Scheduler::defer(static fn() => $semaphore->waitFor($connection));

        $semaphore->waitForRoom();
    }

    IO\write_error_line('');
    IO\write_error_line('Goodbye 👋');

    return 0;
});
