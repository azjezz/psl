<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Network;
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

    public function testSetAndGetReuseAddress(): void
    {
        $socket = TCP\Socket::createV4();

        $socket->setReuseAddress(true);
        static::assertTrue($socket->getReuseAddress());

        $socket->setReuseAddress(false);
        static::assertFalse($socket->getReuseAddress());
    }

    public function testSetAndGetNoDelay(): void
    {
        $socket = TCP\Socket::createV4();

        $socket->setNoDelay(true);
        static::assertTrue($socket->getNoDelay());

        $socket->setNoDelay(false);
        static::assertFalse($socket->getNoDelay());
    }

    public function testConnectAndCommunicate(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently([
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
                $socket->setNoDelay(true);
                $stream = $socket->connect('127.0.0.1', $port);

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
        $socket->setReuseAddress(true);
        $socket->bind('127.0.0.1', 0);
        $listener = $socket->listen();
        $address = $listener->getLocalAddress();

        Async\concurrently([
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

        Async\concurrently([
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
                $stream = $socket->connect('127.0.0.1', $port, Duration::seconds(5));

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

    public function testListenWithoutBindThrows(): void
    {
        $socket = TCP\Socket::createV4();

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot listen without binding');

        $socket->listen();
    }
}
