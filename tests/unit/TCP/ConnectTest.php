<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Str;
use Psl\TCP;

final class ConnectTest extends TestCase
{
    public function testConnect(): void
    {
        Async\concurrently([
            'server' => static function (): void {
                $server = TCP\Server::create('127.0.0.1', 8089);
                self::assertSame('tcp://127.0.0.1:8089', $server->getLocalAddress()->toString());
                $connection = $server->nextConnection();
                $request = $connection->read();
                self::assertSame('Hello, World!', $request);
                $connection->writeAll(Str\reverse($request));
                $connection->close();
                $server->close();
            },
            'client' => static function (): void {
                $client = TCP\connect('127.0.0.1', 8089, TCP\ConnectOptions::create()->withNoDelay(false));

                self::assertSame('tcp://127.0.0.1:8089', $client->getPeerAddress()->toString());
                $client->writeAll('Hello, World!');
                $response = $client->readAll();
                self::assertSame('!dlroW ,olleH', $response);
                $client->close();
            },
        ]);
    }
}
