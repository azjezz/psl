<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Html;
use Psl\IO;
use Psl\Network;
use Psl\Str;
use Psl\TCP;
use Psl\TLS;
use Throwable;

use const SIGINT;

require __DIR__ . '/../../vendor/autoload.php';

const TLS_RESPONSE_FORMAT = <<<HTML
<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>PHP Standard Library - TLS Server</title>
    </head>
    <body>
        <h1>Hello over TLS!</h1>
        <pre><code>%s</code></pre>
    </body>
</html>
HTML;

$certFile = __DIR__ . '/certs/server.crt';
$keyFile = __DIR__ . '/certs/server.key';

$tlsConfig = TLS\ServerConfiguration::create(TLS\Certificate::create(
    $certFile,
    $keyFile,
))->withMinimumVersion(TLS\Version::Tls12);

$acceptor = new TLS\Acceptor($tlsConfig);
$listener = TCP\listen('127.0.0.1', 3443, new TCP\ListenConfiguration(idleConnections: 1024));
$keepaliveTimeout = Duration::seconds(5);

/** @var array<int, TCP\StreamInterface> $active */
$active = [];
$id = 0;

Async\Scheduler::onSignal(SIGINT, static function (string $watcher) use ($listener, &$active): void {
    Async\Scheduler::cancel($watcher);
    $listener->close();

    foreach ($active as $connection) {
        $connection->close();
    }

    $active = [];
});

IO\write_error_line('TLS server is listening on https://127.0.0.1:3443');
IO\write_error_line('Click Ctrl+C to stop the server.');

while (true) {
    try {
        $connection = $listener->accept();
    } catch (Network\Exception\AlreadyStoppedException) {
        break;
    }

    $connectionId = $id++;
    $active[$connectionId] = $connection;

    Async\run::<void>(static function () use ($connection, $acceptor, $keepaliveTimeout, &$active, $connectionId): void {
        try {
            $tls = $acceptor->accept($connection);
            $reader = new IO\Reader($tls);

            while (true) {
                $headers = $reader->readUntil("\r\n\r\n", new Async\TimeoutCancellationToken($keepaliveTimeout));
                if ($headers === null) {
                    break;
                }

                $keepAlive = Str\Byte\contains_ci($headers, 'connection: keep-alive');
                $connectionHeader = $keepAlive ? 'keep-alive' : 'close';

                $body = Str\format(namespace\TLS_RESPONSE_FORMAT, Html\encode_special_characters($headers));
                $tls->writeAll(
                    "HTTP/1.1 200 OK\r\nConnection: {$connectionHeader}\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: "
                    . Str\Byte\length($body)
                    . "\r\n\r\n"
                    . $body,
                );

                if (!$keepAlive) {
                    break;
                }
            }
        } catch (Async\Exception\CancelledException) {
            // @mago-expect lint:no-empty-catch-clause
            // Keep-alive timeout — client didn't send next request in time
        } finally {
            $connection->close();
            unset($active[$connectionId]);
        }
    })->catch::<null>(
        static fn(Throwable $_e): null => (
            // Suppress expected errors during shutdown
            null
        ),
    )->ignore();
}

IO\write_error_line('');
IO\write_error_line('Goodbye 👋');
