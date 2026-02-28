<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\TCP;

final class StreamMethodsTest extends TestCase
{
    private function requireSockets(): void
    {
        if (!extension_loaded('sockets')) {
            static::markTestSkipped('ext-sockets is required for this test.');
        }
    }

    public function testPeekDoesNotConsumeData(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->writeAll('hello');
                // Wait for client to signal done
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                // Peek should return data without consuming
                $peeked = $client->peek(5);
                self::assertSame('hello', $peeked);

                // Data should still be available for normal read
                $data = $client->read(5);
                self::assertSame('hello', $data);

                $client->close();
            },
        ]);
    }

    public function testShutdown(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                // Read until EOF (triggered by client shutdown)
                $data = $connection->readAll();
                self::assertSame('before-shutdown', $data);
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                $client->writeAll('before-shutdown');
                $client->shutdown();
                $client->close();
            },
        ]);
    }

    public function testSetAndGetNoDelay(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                $client->setNoDelay(true);
                self::assertTrue($client->getNoDelay());

                $client->setNoDelay(false);
                self::assertFalse($client->getNoDelay());

                $client->close();
            },
        ]);
    }

    public function testSetAndGetTtl(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                $client->setTtl(64);
                self::assertSame(64, $client->getTtl());

                $client->setTtl(128);
                self::assertSame(128, $client->getTtl());

                $client->close();
            },
        ]);
    }

    public function testSetAndGetKeepAlive(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                $client->setKeepAlive(true);
                self::assertTrue($client->getKeepAlive());

                $client->setKeepAlive(false);
                self::assertFalse($client->getKeepAlive());

                $client->close();
            },
        ]);
    }

    public function testSetAndGetSendBufferSize(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                $client->setSendBufferSize(32_768);
                // The kernel may double the value, so just check it's at least what we set
                self::assertGreaterThanOrEqual(32_768, $client->getSendBufferSize());

                $client->close();
            },
        ]);
    }

    public function testSetAndGetReceiveBufferSize(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                $client->setReceiveBufferSize(32_768);
                // The kernel may double the value, so just check it's at least what we set
                self::assertGreaterThanOrEqual(32_768, $client->getReceiveBufferSize());

                $client->close();
            },
        ]);
    }

    public function testSetAndGetLingerEnabled(): void
    {
        $this->requireSockets();

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->read();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);

                // Enable linger with 5 second timeout
                $client->setLinger(Duration::seconds(5));
                $linger = $client->getLinger();
                self::assertNotNull($linger);
                self::assertSame(5.0, $linger->getTotalSeconds());

                // Enable linger with zero (RST on close)
                $client->setLinger(Duration::zero());
                $linger = $client->getLinger();
                self::assertNotNull($linger);
                self::assertSame(0.0, $linger->getTotalSeconds());

                // Disable linger
                $client->setLinger(null);
                self::assertNull($client->getLinger());

                $client->close();
            },
        ]);
    }
}
