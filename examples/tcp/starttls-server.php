<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\TCP;
use Psl\TLS;

require __DIR__ . '/../../vendor/autoload.php';

$certFile = __DIR__ . '/certs/server.crt';
$keyFile = __DIR__ . '/certs/server.key';

$tlsConfig = TLS\ServerConfiguration::create(TLS\Certificate::create($certFile, $keyFile));

$acceptor = new TLS\Acceptor($tlsConfig);

Async\concurrently([
    'server' => static function () use ($acceptor): void {
        $listener = TCP\listen('localhost', 9025);
        IO\write_error_line('< STARTTLS server listening on port 9025');

        $connection = $listener->accept();
        IO\write_error_line('< client connected');

        // Plain text phase
        $connection->writeAll("220 localhost ESMTP\r\n");
        $command = $connection->read();
        IO\write_error_line('< received: %s', Str\trim($command));

        $connection->writeAll("250-localhost\r\n250 STARTTLS\r\n");
        $command = $connection->read();
        IO\write_error_line('< received: %s', Str\trim($command));

        $connection->writeAll("220 Ready to start TLS\r\n");

        // Upgrade to TLS
        IO\write_error_line('< upgrading to TLS...');
        $tls = $acceptor->accept($connection);
        IO\write_error_line('< TLS handshake complete: %s', $tls->getState()->version->name);

        // Encrypted phase
        $command = $tls->read();
        IO\write_error_line('< (encrypted) received: %s', Str\trim($command));

        $tls->writeAll("250 OK\r\n");
        $tls->close();

        $listener->close();
    },
    'client' => static function (): void {
        $client = TCP\connect('localhost', 9025);

        $greeting = $client->read();
        IO\write_error_line('> received: %s', Str\trim($greeting));

        $client->writeAll("EHLO client\r\n");
        $capabilities = $client->read();
        IO\write_error_line('> received: %s', Str\trim($capabilities));

        $client->writeAll("STARTTLS\r\n");
        $response = $client->read();
        IO\write_error_line('> received: %s', Str\trim($response));

        // Upgrade to TLS
        IO\write_error_line('> upgrading to TLS...');
        $connector = new TLS\Connector(new TLS\ClientConfiguration(peerVerification: false, allowSelfSigned: true));
        $tls = $connector->connect($client, 'localhost');
        IO\write_error_line('> TLS handshake complete: %s', $tls->getState()->version->name);

        // Encrypted phase
        $tls->writeAll("EHLO client\r\n");
        $response = $tls->read();
        IO\write_error_line('> (encrypted) received: %s', Str\trim($response));

        $tls->close();
    },
]);
