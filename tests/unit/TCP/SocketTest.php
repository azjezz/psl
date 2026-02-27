<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network;
use Psl\TCP;

final class SocketTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\extension_loaded('sockets')) {
            static::markTestSkipped('ext-sockets is required for TCP\\Socket tests.');
        }
    }

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
        static::assertNotNull($address->port);
        static::assertGreaterThan(0, $address->port);
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

    public function testSetAndGetSendBufferSize(): void
    {
        $socket = TCP\Socket::createV4();

        $socket->setSendBufferSize(32_768);
        // Kernel may double the value
        static::assertGreaterThanOrEqual(32_768, $socket->getSendBufferSize());
    }

    public function testSetAndGetReceiveBufferSize(): void
    {
        $socket = TCP\Socket::createV4();

        $socket->setReceiveBufferSize(32_768);
        static::assertGreaterThanOrEqual(32_768, $socket->getReceiveBufferSize());
    }

    public function testSetAndGetKeepAlive(): void
    {
        $socket = TCP\Socket::createV4();

        $socket->setKeepAlive(true);
        static::assertTrue($socket->getKeepAlive());

        $socket->setKeepAlive(false);
        static::assertFalse($socket->getKeepAlive());
    }

    public function testConnectAndCommunicate(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

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
        $address = $socket->getLocalAddress();
        $listener = $socket->listen();

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
                $client = TCP\connect('127.0.0.1', $address->port);
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

        // Should throw — socket already consumed by listen()
        $socket->bind('127.0.0.1', 0);

        $listener->close();
    }
}
