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

Async\main(static function (): int {
    $cert_file = __DIR__ . '/certs/server.crt';
    $key_file = __DIR__ . '/certs/server.key';

    $tls_config = TLS\ServerConfig::create(TLS\Certificate::create(
        $cert_file,
        $key_file,
    ))->withMinimumVersion(TLS\Version::Tls12);

    $acceptor = new TLS\Acceptor($tls_config);
    $listener = TCP\listen('127.0.0.1', 3443, idle_connections: 1024);
    $keepalive_timeout = Duration::seconds(5);

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

        $connection_id = $id++;
        $active[$connection_id] = $connection;

        Async\run(static function () use ($connection, $acceptor, $keepalive_timeout, &$active, $connection_id): void {
            try {
                $tls = $acceptor->accept($connection);
                $reader = new IO\Reader($tls);

                while (true) {
                    $headers = $reader->readUntil("\r\n\r\n", $keepalive_timeout);
                    if ($headers === null) {
                        // @mago-expect lint:excessive-nesting
                        break;
                    }

                    $keep_alive = Str\Byte\contains_ci($headers, 'connection: keep-alive');
                    $connection_header = $keep_alive ? 'keep-alive' : 'close';

                    $body = Str\format(TLS_RESPONSE_FORMAT, Html\encode_special_characters($headers));
                    $tls->writeAll(
                        "HTTP/1.1 200 OK\r\nConnection: {$connection_header}\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: "
                        . Str\Byte\length($body)
                        . "\r\n\r\n"
                        . $body,
                    );

                    if (!$keep_alive) {
                        // @mago-expect lint:excessive-nesting
                        break;
                    }
                }
            } catch (IO\Exception\TimeoutException) {
                // @mago-expect lint:no-empty-catch-clause
                // Keep-alive timeout — client didn't send next request in time
            } finally {
                $connection->close();
                unset($active[$connection_id]);
            }
        })->catch(
            static fn(Throwable $e): null => (
                // Suppress expected errors during shutdown
                null
            ),
        )->ignore();
    }

    IO\write_error_line('');
    IO\write_error_line('Goodbye 👋');

    return 0;
});
