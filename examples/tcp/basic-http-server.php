<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\File;
use Psl\Html;
use Psl\IO;
use Psl\Network;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Note: This example is purely for demonstration purposes, and should never be used in a production environment.
 *
 * Generate a self-signed certificate using the following command:
 *
 * $ cd examples/tcp/fixtures
 * $ openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout privatekey.pem -out certificate.pem -config openssl.cnf
 */
$server = TCP\Server::create('localhost', 3030, TCP\ServerOptions::default()
    ->withTlsServerOptions(
        TCP\TLS\ServerOptions::default()
            ->withMinimumVersion(TCP\TLS\Version::Tls12)
            ->withAllowSelfSignedCertificates()
            ->withPeerVerification(false)
            ->withSecurityLevel(TCP\TLS\SecurityLevel::Level2)
            ->withDefaultCertificate(TCP\TLS\Certificate::create(
                certificate_file: __DIR__ . '/fixtures/certificate.pem',
                key_file: __DIR__ . '/fixtures/privatekey.pem',
            ))
    )
);

Async\Scheduler::onSignal(SIGINT, $server->close(...));

IO\write_error_line('Server is listening on https://localhost:3030');
IO\write_error_line('Click Ctrl+C to stop the server.');

while (true) {
    try {
        $connection = $server->nextConnection();

        Async\Scheduler::defer(static fn() => handle($connection));
    } catch (TCP\TLS\Exception\NegotiationException $e) {
        IO\write_error_line('[SRV]: error "%s" at %s:%d"', $e->getMessage(), $e->getFile(), $e->getLine());

        continue;
    } catch (Network\Exception\AlreadyStoppedException $e) {
        break;
    }
}

IO\write_error_line('');
IO\write_error_line('Goodbye 👋');

function handle(Network\SocketInterface $connection): void
{
    try {
        $peer = $connection->getPeerAddress();

        IO\write_error_line('[SRV]: received a connection from peer "%s".', $peer);

        do {
            $request = $connection->read();

            $template = File\read(__DIR__ . '/templates/index.html');
            $content = Str\format($template, Html\encode_special_characters($request));
            $length = Str\Byte\length($content);

            $connection->writeAll("HTTP/1.1 200 OK\nConnection: keep-alive\nContent-Type: text/html; charset=utf-8\nContent-Length: $length\n\n");
            $connection->writeAll($content);
        } while(!$connection->reachedEndOfDataSource());

        IO\write_error_line('[SRV]: connection dropped by peer "%s".', $peer);
    } catch (IO\Exception\ExceptionInterface $e) {
        if (!$connection->reachedEndOfDataSource()) {
            // If we reached end of data source ( EOF ) and gotten an error, that means that connect was most likely dropped
            // by peer while we are performing a write operation, ignore it.
            //
            // otherwise, log the error:
            IO\write_error_line('[SRV]: error "%s" at %s:%d"', $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }  finally {
        $connection->close();
    }
}
