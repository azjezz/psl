<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\UDP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\UDP;

final class ConnectTest extends TestCase
{
    public function testConnectReturnsConnectedSocket(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $server_addr = $server->getLocalAddress();

            $connected = UDP\connect($server_addr->host, $server_addr->port);

            static::assertInstanceOf(UDP\ConnectedSocket::class, $connected);
            static::assertSame($server_addr->host, $connected->getPeerAddress()->host);
            static::assertSame($server_addr->port, $connected->getPeerAddress()->port);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testConnectCanSendAndReceive(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $server_addr = $server->getLocalAddress();

            $connected = UDP\connect($server_addr->host, $server_addr->port);

            $connected->send('hello');

            [$data, $from] = $server->receiveFrom(1024);
            static::assertSame('hello', $data);

            $server->sendTo('world', $from);

            $response = $connected->receive(1024);
            static::assertSame('world', $response);

            $connected->close();
            $server->close();
        })->await();
    }
}
