<?php

declare(strict_types=1);

namespace Psl\TCP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\TCP;

final class StreamMethodsTest extends TestCase
{
    public function testPeekDoesNotConsumeData(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently::<string, void>([
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

        Async\concurrently::<string, void>([
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
}
