<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\TCP;

final class SocketPoolTest extends TestCase
{
    public function testCheckoutCreatesNewConnection(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $data = $connection->read();
                self::assertSame('pool-test', $data);
                $connection->writeAll('pool-ok');
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $pool = new TCP\SocketPool();
                $stream = $pool->checkout('127.0.0.1', $port);
                $stream->writeAll('pool-test');
                $response = $stream->readAll();
                self::assertSame('pool-ok', $response);
                $pool->close();
            },
        ]);
    }

    public function testCheckinAndReuseConnection(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                // Accept first use
                $connection = $listener->accept();
                $data = $connection->read();
                self::assertSame('first', $data);
                $connection->writeAll('first-ok');

                // The same connection will be reused, read again
                $data2 = $connection->read();
                self::assertSame('second', $data2);
                $connection->writeAll('second-ok');
                $connection->close();

                $listener->close();
            },
            'client' => static function () use ($port): void {
                $pool = new TCP\SocketPool();

                // First checkout
                $stream = $pool->checkout('127.0.0.1', $port);
                $stream->writeAll('first');
                $response = $stream->read();
                self::assertSame('first-ok', $response);

                // Return to pool
                $pool->checkin($stream);

                // Second checkout should reuse the same connection
                $stream2 = $pool->checkout('127.0.0.1', $port);
                $stream2->writeAll('second');
                $response2 = $stream2->read();
                self::assertSame('second-ok', $response2);

                $pool->clear($stream2);
                $pool->close();
            },
        ]);
    }

    public function testClearRemovesFromPool(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                // First connection
                $connection1 = $listener->accept();
                $data1 = $connection1->read();
                self::assertSame('conn1', $data1);
                $connection1->writeAll('ok1');
                $connection1->close();

                // Second connection (new, because first was cleared)
                $connection2 = $listener->accept();
                $data2 = $connection2->read();
                self::assertSame('conn2', $data2);
                $connection2->writeAll('ok2');
                $connection2->close();

                $listener->close();
            },
            'client' => static function () use ($port): void {
                $pool = new TCP\SocketPool();

                $stream1 = $pool->checkout('127.0.0.1', $port);
                $stream1->writeAll('conn1');
                $stream1->read();

                // Clear instead of checkin — removes from pool
                $pool->clear($stream1);

                // This should create a new connection since pool is empty
                $stream2 = $pool->checkout('127.0.0.1', $port);
                $stream2->writeAll('conn2');
                $response = $stream2->readAll();
                self::assertSame('ok2', $response);

                $pool->close();
            },
        ]);
    }

    public function testCheckinUnknownStreamIsIgnored(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $pool = new TCP\SocketPool();

                // Create a stream outside the pool
                $stream = TCP\connect('127.0.0.1', $port);

                // Checkin a stream that was never checked out — should be silently ignored
                $pool->checkin($stream);

                // Verify stream is still usable (checkin didn't close it)
                $resource = $stream->getStream();
                self::assertNotNull($resource);

                $stream->close();
                $pool->close();
            },
        ]);
    }

    public function testCloseClosesAllIdleConnections(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $data = $connection->read();
                self::assertSame('x', $data);
                $connection->writeAll('a');

                // Pool will close the connection — read should return empty/EOF
                $remaining = $connection->readAll();
                self::assertSame('', $remaining);
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $pool = new TCP\SocketPool();

                $stream = $pool->checkout('127.0.0.1', $port);
                $stream->writeAll('x');
                $stream->read();
                $pool->checkin($stream);

                // Close pool — should close the idle connection
                $pool->close();
            },
        ]);
    }

    public function testCustomConnector(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $data = $connection->read();
                self::assertSame('custom', $data);
                $connection->writeAll('ok');
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                // Use StaticConnector so pool always connects to our test server
                $connector = new TCP\StaticConnector('127.0.0.1', $port);
                $pool = new TCP\SocketPool(connector: $connector);

                $stream = $pool->checkout('anything', 9999);
                $stream->writeAll('custom');
                $response = $stream->readAll();
                self::assertSame('ok', $response);

                $pool->close();
            },
        ]);
    }
}
