<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\TCP;
use Psl\TLS;

final class LazyAcceptorTest extends TestCase
{
    private const CERT_FILE = __DIR__ . '/../../fixture/certs/server.crt';
    private const KEY_FILE = __DIR__ . '/../../fixture/certs/server.key';

    public function testLazyAcceptorInspectsClientHelloAndCompletes(): void
    {
        $cert = TLS\Certificate::create(self::CERT_FILE, self::KEY_FILE);
        $serverConfig = TLS\ServerConfiguration::create($cert);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $serverConfig): void {
                $connection = $listener->accept();
                $lazy = TLS\LazyAcceptor::default();
                $hello = $lazy->accept($connection);

                // ClientHello should have server name from the client's SNI
                static::assertSame('localhost', $hello->getServerName());

                $tls = $hello->complete($serverConfig);
                static::assertInstanceOf(TLS\StreamInterface::class, $tls);

                $data = $tls->read();
                static::assertSame('lazy-hello', $data);
                $tls->writeAll('lazy-response');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfiguration::default()->withPeerVerification(false)->withAllowSelfSigned(true),
                );
                $client = $connector->connect($stream, 'localhost');

                $client->writeAll('lazy-hello');
                $response = $client->readAll();
                static::assertSame('lazy-response', $response);
                $client->close();
            },
        ]);
    }

    public function testLazyAcceptorWithAlpnProtocols(): void
    {
        $cert = TLS\Certificate::create(self::CERT_FILE, self::KEY_FILE);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $cert): void {
                $connection = $listener->accept();
                $lazy = TLS\LazyAcceptor::default();
                $hello = $lazy->accept($connection);

                $alpn = $hello->getAlpnProtocols();
                static::assertNotNull($alpn);
                static::assertContains('h2', $alpn);
                static::assertContains('http/1.1', $alpn);

                $config = TLS\ServerConfiguration::create($cert)->withAlpnProtocols(['h2', 'http/1.1']);

                $tls = $hello->complete($config);

                $state = $tls->getState();
                static::assertSame('h2', $state->alpnProtocol);

                $tls->writeAll('ok');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfiguration::default()
                        ->withPeerVerification(false)
                        ->withAllowSelfSigned(true)
                        ->withAlpnProtocols(['h2', 'http/1.1']),
                );
                $client = $connector->connect($stream, 'localhost');

                static::assertSame('h2', $client->getState()->alpnProtocol);

                $data = $client->readAll();
                static::assertSame('ok', $data);
                $client->close();
            },
        ]);
    }

    public function testDefault(): void
    {
        $lazy = TLS\LazyAcceptor::default();

        static::assertInstanceOf(TLS\LazyAcceptor::class, $lazy);
    }
}
