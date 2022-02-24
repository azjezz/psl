<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\Html;
use Psl\IO;
use Psl\Str;
use Psl\Iter;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

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

$server = TCP\Server::create('localhost', 3030);

Async\Scheduler::unreference(Async\Scheduler::onSignal(SIGINT, $server->close(...)));

IO\write_error_line('Server is listening on http://localhost:3030');
IO\write_error_line('Click Ctrl+C to stop the server.');

Iter\apply($server->incoming(), static function ($connection): void {
    $request = $connection->read();
    $connection->write("HTTP/1.1 200 OK\nConnection: close\nContent-Type: text/html; charset=utf-8\n\n");
    $connection->write(Str\format(RESPONSE_FORMAT, Html\encode_special_characters($request)));
    $connection->close();
});

IO\write_error_line('');
IO\write_error_line('Goodbye 👋');
