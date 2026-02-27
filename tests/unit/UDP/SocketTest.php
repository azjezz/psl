<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\UDP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Network;
use Psl\UDP;

final class SocketTest extends TestCase
{
    public function testBindAndGetLocalAddress(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $address = $socket->getLocalAddress();
            self::assertSame('127.0.0.1', $address->host);
            self::assertGreaterThan(0, $address->port);
            self::assertSame(Network\SocketScheme::Udp, $address->scheme);
            $socket->close();
        })->await();
    }

    public function testSendToAndReceiveFrom(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $target = $receiver->getLocalAddress();
            $sender->sendTo('hello', $target);

            [$data, $from] = $receiver->receiveFrom(1024);
            self::assertSame('hello', $data);
            self::assertSame('127.0.0.1', $from->host);
            self::assertSame($sender->getLocalAddress()->port, $from->port);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testConnectAndSendReceive(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);

            $server_addr = $server->getLocalAddress();
            $client->connect($server_addr->host, $server_addr->port);

            $client->send('ping');

            [$data, $from] = $server->receiveFrom(1024);
            self::assertSame('ping', $data);

            $server->sendTo('pong', $from);

            $response = $client->receive(1024);
            self::assertSame('pong', $response);

            $client->close();
            $server->close();
        })->await();
    }

    public function testSendRequiresConnected(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot send on an unconnected socket');

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->send('data');
            } finally {
                $socket->close();
            }
        })->await();
    }

    public function testReceiveRequiresConnected(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot receive on an unconnected socket');

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->receive(1024);
            } finally {
                $socket->close();
            }
        })->await();
    }

    public function testSendToForbiddenWhenConnected(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot use sendTo()');

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $client->sendTo('data', Network\Address::udp('127.0.0.1', 9999));
            } finally {
                $client->close();
                $server->close();
            }
        })->await();
    }

    public function testReceiveFromForbiddenWhenConnected(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot use receiveFrom()');

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $client->receiveFrom(1024);
            } finally {
                $client->close();
                $server->close();
            }
        })->await();
    }

    public function testPeek(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $sender->sendTo('peek-test', $receiver->getLocalAddress());

            // Peek should return data without consuming
            $peeked = $receiver->peek(1024);
            self::assertSame('peek-test', $peeked);

            // Data should still be available for receiveFrom
            [$data] = $receiver->receiveFrom(1024);
            self::assertSame('peek-test', $data);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testPeekFrom(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $sender->sendTo('peek-from-test', $receiver->getLocalAddress());

            [$data, $from] = $receiver->peekFrom(1024);
            self::assertSame('peek-from-test', $data);
            self::assertSame('127.0.0.1', $from->host);
            self::assertSame($sender->getLocalAddress()->port, $from->port);

            // Data should still be available
            [$data2] = $receiver->receiveFrom(1024);
            self::assertSame('peek-from-test', $data2);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testGetPeerAddressNullWhenNotConnected(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            self::assertNull($socket->getPeerAddress());
            $socket->close();
        })->await();
    }

    public function testGetPeerAddressWhenConnected(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $addr = $server->getLocalAddress();
            $client->connect($addr->host, $addr->port);

            $peer = $client->getPeerAddress();
            self::assertNotNull($peer);
            self::assertSame('127.0.0.1', $peer->host);
            self::assertSame($addr->port, $peer->port);

            $client->close();
            $server->close();
        })->await();
    }

    public function testCloseThrowsOnSubsequentUse(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->getLocalAddress();
        })->await();
    }

    public function testSetAndGetBroadcast(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);

            $socket->setBroadcast(true);
            self::assertTrue($socket->getBroadcast());

            $socket->setBroadcast(false);
            self::assertFalse($socket->getBroadcast());

            $socket->close();
        })->await();
    }

    public function testSetAndGetTtl(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);

            $socket->setTtl(64);
            self::assertSame(64, $socket->getTtl());

            $socket->setTtl(128);
            self::assertSame(128, $socket->getTtl());

            $socket->close();
        })->await();
    }

    public function testPayloadSizeValidation(): void
    {
        $this->expectException(Network\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum size');

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->sendTo(str_repeat('x', 65_508), Network\Address::udp('127.0.0.1', 9999));
            } finally {
                $socket->close();
            }
        })->await();
    }

    public function testReceiveFromTimeout(): void
    {
        $this->expectException(IO\Exception\TimeoutException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->receiveFrom(1024, Duration::milliseconds(50));
            } finally {
                $socket->close();
            }
        })->await();
    }
}
