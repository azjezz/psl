<?php

declare(strict_types=1);

namespace Psl\UDP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\UDP;

final class ConnectTest extends TestCase
{
    public function testConnectReturnsConnectedSocket(): void
    {
        Async\run::<void>(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $serverAddr = $server->getLocalAddress();

            $connected = UDP\connect($serverAddr->host, $serverAddr->port);

            static::assertInstanceOf(UDP\ConnectedSocket::class, $connected);
            static::assertSame($serverAddr->host, $connected->getPeerAddress()->host);
            static::assertSame($serverAddr->port, $connected->getPeerAddress()->port);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testConnectCanSendAndReceive(): void
    {
        Async\run::<void>(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $serverAddr = $server->getLocalAddress();

            $connected = UDP\connect($serverAddr->host, $serverAddr->port);

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
