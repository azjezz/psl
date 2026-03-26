<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Network;
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

    public function testAcceptThrowsOnClosedStream(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource is not available.');

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->close();

                $lazy = new TLS\LazyAcceptor();
                $lazy->accept($connection);
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                Async\sleep(Duration::milliseconds(50));
                $client->close();
            },
        ]);

        $listener->close();
    }

    public function testAcceptThrowsWhenAlreadyCancelled(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();

                $token = new Async\SignalCancellationToken();
                $token->cancel();

                $lazy = new TLS\LazyAcceptor();
                $lazy->accept($connection, $token);
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                Async\sleep(Duration::milliseconds(100));
                $client->close();
            },
        ]);

        $listener->close();
    }

    public function testAcceptThrowsWhenCancelledDuringWait(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        $signal = new Async\SignalCancellationToken();

        Async\concurrently([
            'server' => static function () use ($listener, $signal): void {
                $connection = $listener->accept();
                $lazy = new TLS\LazyAcceptor();
                $lazy->accept($connection, $signal);
            },
            'cancel' => static function () use ($signal): void {
                Async\sleep(Duration::milliseconds(30));
                $signal->cancel();
            },
            'client' => static function () use ($port, $listener): void {
                $client = TCP\connect('127.0.0.1', $port);
                Async\sleep(Duration::milliseconds(200));
                $client->close();
                $listener->close();
            },
        ]);
    }

    public function testAcceptThrowsOnEmptyPeekData(): void
    {
        $this->expectException(TLS\Exception\HandshakeFailedException::class);
        $this->expectExceptionMessage('Failed to peek ClientHello data.');

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $lazy = new TLS\LazyAcceptor();
                $lazy->accept($connection);
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                $client->shutdown();
                Async\sleep(Duration::milliseconds(100));
                $client->close();
            },
        ]);

        $listener->close();
    }
}
