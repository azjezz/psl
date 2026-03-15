<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\UDP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Network;
use Psl\UDP;

final class ConnectedSocketTest extends TestCase
{
    public function testSendAndReceive(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $connected = $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->send('ping');

            [$data, $from] = $server->receiveFrom(1024);
            static::assertSame('ping', $data);

            $server->sendTo('pong', $from);

            $response = $connected->receive(1024);
            static::assertSame('pong', $response);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testSendReturnsByteCount(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $bytesSent = $connected->send('connected-data');
            static::assertSame(14, $bytesSent);

            [$data] = $server->receiveFrom(1024);
            static::assertSame('connected-data', $data);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testPeekDoesNotConsumeData(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->send('peek-test');

            [$data, $from] = $server->receiveFrom(1024);
            $server->sendTo('response', $from);

            $peeked = $connected->peek(1024);
            static::assertSame('response', $peeked);

            $received = $connected->receive(1024);
            static::assertSame('response', $received);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testSendWithTimeout(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $bytesSent = $connected->send('timeout-send', new Async\TimeoutCancellationToken(Duration::seconds(5)));
            static::assertSame(12, $bytesSent);

            [$data] = $server->receiveFrom(1024);
            static::assertSame('timeout-send', $data);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testReceiveTimeout(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $connected->receive(1024, new Async\TimeoutCancellationToken(Duration::milliseconds(50)));
            } finally {
                $connected->close();
                $server->close();
            }
        })->await();
    }

    public function testPayloadSizeValidationOnSend(): void
    {
        $this->expectException(Network\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum size');

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $connected->send(str_repeat('x', 65_508));
            } finally {
                $connected->close();
                $server->close();
            }
        })->await();
    }

    public function testGetPeerAddress(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $serverAddr = $server->getLocalAddress();
            $connected = UDP\connect($serverAddr->host, $serverAddr->port);

            $peer = $connected->getPeerAddress();
            static::assertSame('127.0.0.1', $peer->host);
            static::assertSame($serverAddr->port, $peer->port);
            static::assertSame(Network\SocketScheme::Udp, $peer->scheme);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testGetLocalAddress(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $address = $connected->getLocalAddress();
            static::assertSame('127.0.0.1', $address->host);
            static::assertGreaterThan(0, $address->port);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testGetStreamReturnsResource(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            static::assertIsResource($connected->getStream());

            $connected->close();
            $server->close();
        })->await();
    }

    public function testGetStreamReturnsNullAfterClose(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            static::assertNull($connected->getStream());

            $server->close();
        })->await();
    }

    public function testCloseThrowsOnSubsequentUse(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            $server->close();

            $connected->send('data');
        })->await();
    }

    public function testDoubleCloseDoesNotThrow(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            $connected->close();
            static::assertNull($connected->getStream());

            $server->close();
        })->await();
    }

    public function testSendOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            $server->close();

            $connected->send('data');
        })->await();
    }

    public function testReceiveOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            $server->close();

            $connected->receive(1024);
        })->await();
    }

    public function testPeekOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $connected->close();
            $server->close();

            $connected->peek(1024);
        })->await();
    }

    public function testPeekTimeout(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $connected = UDP\connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $connected->peek(1024, new Async\TimeoutCancellationToken(Duration::milliseconds(50)));
            } finally {
                $connected->close();
                $server->close();
            }
        })->await();
    }
}
