<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\Str\Byte;
use Psl\TCP;
use Psl\TLS;

require __DIR__ . '/../../vendor/autoload.php';

$certFile = __DIR__ . '/certs/server.crt';
$keyFile = __DIR__ . '/certs/server.key';

$tlsConfig = TLS\ServerConfiguration::create(TLS\Certificate::create($certFile, $keyFile));

Async\concurrently([
    'server' => static function () use ($tlsConfig): void {
        $lazyAcceptor = new TLS\LazyAcceptor();
        $listener = TCP\listen('localhost', 8443);
        IO\write_error_line('< server listening on port 8443 (HTTP + HTTPS)');

        // Handle 2 connections
        for ($i = 0; $i < 2; $i++) {
            $connection = $listener->accept();

            // Peek first byte to detect TLS ClientHello (starts with 0x16)
            $firstByte = $connection->peek(1);

            if (Byte\ord($firstByte) === 0x16) {
                // TLS connection detected
                IO\write_error_line('< TLS ClientHello detected, performing handshake...');

                $hello = $lazyAcceptor->accept($connection);
                IO\write_error_line(
                    '< SNI: %s, ALPN: %s',
                    $hello->getServerName() ?? '(none)',
                    Str\join($hello->getAlpnProtocols() ?? ['(none)'], ','),
                );

                $tls = $hello->complete($tlsConfig);
                IO\write_error_line('< TLS handshake complete');

                $request = $tls->read();
                $tls->writeAll("HTTP/1.1 200 OK\r\nConnection: close\r\n\r\nHello over HTTPS!\r\n");
                $tls->close();

                continue;
            }

            // Plain HTTP
            IO\write_error_line('< plain HTTP detected');

            $request = $connection->read();
            $connection->writeAll("HTTP/1.1 200 OK\r\nConnection: close\r\n\r\nHello over HTTP!\r\n");
            $connection->close();
        }

        $listener->close();
    },
    'plain_client' => static function (): void {
        // Plain HTTP request
        $client = TCP\connect('localhost', 8443);
        $client->writeAll("GET / HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");
        $response = $client->readAll();
        IO\write_error_line('> HTTP response: %s', Str\trim($response));
        $client->close();
    },
    'tls_client' => static function (): void {
        // HTTPS request
        $client = TCP\connect('localhost', 8443);
        $connector = new TLS\Connector(new TLS\ClientConfiguration(peerVerification: false, allowSelfSigned: true));

        $tls = $connector->connect($client, 'localhost');
        $tls->writeAll("GET / HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");
        $response = $tls->readAll();
        IO\write_error_line('> HTTPS response: %s', Str\trim($response));
        $tls->close();
    },
]);
