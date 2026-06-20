<?php

declare(strict_types=1);

namespace Psl\TCP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Network;
use Psl\OS;
use Psl\TCP;

final class SocketTest extends TestCase
{
    public function testCreateV4(): void
    {
        $socket = TCP\Socket::createV4();

        static::assertInstanceOf(TCP\Socket::class, $socket);
    }

    public function testCreateV6(): void
    {
        $socket = TCP\Socket::createV6();

        static::assertInstanceOf(TCP\Socket::class, $socket);
    }

    public function testBindAndGetLocalAddress(): void
    {
        $socket = TCP\Socket::createV4();
        $socket->bind('127.0.0.1', 0);

        $address = $socket->getLocalAddress();
        static::assertSame('127.0.0.1', $address->host);
        static::assertSame(0, $address->port);
    }

    public function testListenWithConfiguration(): void
    {
        $socket = TCP\Socket::createV4();
        $socket->bind('127.0.0.1', 0);
        $listener = $socket->listen(new TCP\ListenConfiguration(noDelay: true, reuseAddress: true, backlog: 64));

        $address = $listener->getLocalAddress();
        static::assertSame('127.0.0.1', $address->host);

        $listener->close();
    }

    public function testConnectWithConfiguration(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently::<string, void>([
            'server' => static function () use ($listener): void {
                $conn = $listener->accept();
                $data = $conn->read();
                static::assertSame('config-hello', $data);
                $conn->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $socket = TCP\Socket::createV4();
                $stream = $socket->connect('127.0.0.1', $port, new TCP\ConnectConfiguration(noDelay: true));

                $stream->writeAll('config-hello');
                $stream->close();
            },
        ]);
    }

    public function testConnectAndCommunicate(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently::<string, void>([
            'server' => static function () use ($listener): void {
                $conn = $listener->accept();
                $data = $conn->read();
                static::assertSame('socket-hello', $data);
                $conn->writeAll('socket-response');
                $conn->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $socket = TCP\Socket::createV4();
                $stream = $socket->connect('127.0.0.1', $port, new TCP\ConnectConfiguration(noDelay: true));

                $stream->writeAll('socket-hello');
                $response = $stream->readAll();
                static::assertSame('socket-response', $response);
                $stream->close();
            },
        ]);
    }

    public function testListenAndAccept(): void
    {
        $socket = TCP\Socket::createV4();
        $socket->bind('127.0.0.1', 0);
        $listener = $socket->listen(new TCP\ListenConfiguration(reuseAddress: true));
        $address = $listener->getLocalAddress();

        Async\concurrently::<string, void>([
            'server' => static function () use ($listener): void {
                $conn = $listener->accept();
                $data = $conn->read();
                static::assertSame('ping', $data);
                $conn->writeAll('pong');
                $conn->close();
                $listener->close();
            },
            'client' => static function () use ($address): void {
                $client = TCP\connect('127.0.0.1', $address->port ?? 0);
                $client->writeAll('ping');
                $response = $client->readAll();
                static::assertSame('pong', $response);
                $client->close();
            },
        ]);
    }

    public function testConsumedSocketThrows(): void
    {
        $socket = TCP\Socket::createV4();
        $socket->bind('127.0.0.1', 0);
        $listener = $socket->listen();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Socket has already been consumed');

        $socket->bind('127.0.0.1', 0);

        $listener->close();
    }

    public function testConnectWithTimeout(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently::<string, void>([
            'server' => static function () use ($listener): void {
                $conn = $listener->accept();
                $data = $conn->read();
                static::assertSame('with-timeout', $data);
                $conn->writeAll('ok');
                $conn->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $socket = TCP\Socket::createV4();
                $stream = $socket->connect(
                    '127.0.0.1',
                    $port,
                    cancellation: new Async\TimeoutCancellationToken(Duration::seconds(5)),
                );

                $stream->writeAll('with-timeout');
                $response = $stream->readAll();
                static::assertSame('ok', $response);
                $stream->close();
            },
        ]);
    }

    public function testConnectConsumedByConnect(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        $socket = TCP\Socket::createV4();
        $stream = $socket->connect('127.0.0.1', $port);
        $stream->close();
        $listener->close();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Socket has already been consumed');

        $socket->connect('127.0.0.1', $port);
    }

    public function testGetLocalAddressOnConsumedSocketThrows(): void
    {
        $socket = TCP\Socket::createV4();
        $socket->bind('127.0.0.1', 0);
        $listener = $socket->listen();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Socket has already been consumed');

        $socket->getLocalAddress();

        $listener->close();
    }

    public function testGetLocalAddressWithoutBindThrows(): void
    {
        $socket = TCP\Socket::createV4();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Socket has not been bound');

        $socket->getLocalAddress();
    }

    public function testCreateV6BindAndListen(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('unsupported OS');
        }

        $socket = TCP\Socket::createV6();
        $socket->bind('::1', 0);

        try {
            $listener = $socket->listen(new TCP\ListenConfiguration(noDelay: true, reuseAddress: true));
        } catch (Network\Exception\RuntimeException) {
            $this->addToAssertionCount(1);
            return;
        }

        $address = $listener->getLocalAddress();
        static::assertSame('::1', $address->host);

        $listener->close();
    }

    public function testListenWithoutBindThrows(): void
    {
        $socket = TCP\Socket::createV4();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot listen without binding');

        $socket->listen();
    }

    public function testConnectWithBindToConfiguration(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently::<string, void>([
            'server' => static function () use ($listener): void {
                $conn = $listener->accept();
                $data = $conn->read();
                static::assertSame('bindto-hello', $data);
                $conn->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $socket = TCP\Socket::createV4();
                $stream = $socket->connect('127.0.0.1', $port, new TCP\ConnectConfiguration(bindTo: '127.0.0.1:0'));

                $local = $stream->getLocalAddress();
                static::assertSame('127.0.0.1', $local->host);
                static::assertGreaterThan(0, $local->port);

                $stream->writeAll('bindto-hello');
                $stream->close();
            },
        ]);
    }
}
