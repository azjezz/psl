<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\TCP;
use Psl\TLS;

final class TCPConnectorTest extends TestCase
{
    private const string CERT_FILE = __DIR__ . '/../../fixture/certs/server.crt';
    private const string KEY_FILE = __DIR__ . '/../../fixture/certs/server.key';

    public function testTCPConnectorImplementsConnectorInterface(): void
    {
        $connector = new TLS\TCPConnector(new TCP\Connector(), TLS\Connector::default());

        static::assertInstanceOf(TCP\ConnectorInterface::class, $connector);
    }

    public function testTCPConnectorConnectsAndUpgradesToTls(): void
    {
        $cert = TLS\Certificate::create(self::CERT_FILE, self::KEY_FILE);
        $serverConfig = TLS\ServerConfiguration::create($cert);
        $acceptor = new TLS\Acceptor($serverConfig);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $acceptor): void {
                $connection = $listener->accept();
                $tls = $acceptor->accept($connection);
                $data = $tls->read();
                static::assertSame('hello from tcp-connector', $data);
                $tls->writeAll('hello back');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $config = TLS\ClientConfiguration::default()->withPeerVerification(false)->withAllowSelfSigned(true);

                $connector = new TLS\TCPConnector(new TCP\Connector(), new TLS\Connector($config));

                $stream = $connector->connect('127.0.0.1', $port);

                static::assertInstanceOf(TCP\StreamInterface::class, $stream);
                static::assertInstanceOf(TLS\StreamInterface::class, $stream);

                $stream->writeAll('hello from tcp-connector');
                $response = $stream->readAll();
                static::assertSame('hello back', $response);
                $stream->close();
            },
        ]);
    }

    public function testTCPConnectorWorksWithSocketPool(): void
    {
        $cert = TLS\Certificate::create(self::CERT_FILE, self::KEY_FILE);
        $serverConfig = TLS\ServerConfiguration::create($cert);
        $acceptor = new TLS\Acceptor($serverConfig);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $acceptor): void {
                $connection = $listener->accept();
                $tls = $acceptor->accept($connection);
                $data = $tls->read();
                static::assertSame('pooled-request', $data);
                $tls->writeAll('pooled-response');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $config = TLS\ClientConfiguration::default()->withPeerVerification(false)->withAllowSelfSigned(true);

                $pool = new TCP\SocketPool(new TLS\TCPConnector(new TCP\Connector(), new TLS\Connector($config)));

                $stream = $pool->checkout('127.0.0.1', $port);
                static::assertInstanceOf(TLS\StreamInterface::class, $stream);

                $stream->writeAll('pooled-request');
                $response = $stream->readAll();
                static::assertSame('pooled-response', $response);

                $pool->clear($stream);
                $pool->close();
            },
        ]);
    }
}
