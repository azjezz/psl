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

    public function testBindWithExplicitPort0(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $address = $socket->getLocalAddress();
            self::assertGreaterThan(0, $address->port);
            $socket->close();
        })->await();
    }

    public function testBindWithExplicitFalseOptions(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0, false, false, false);
            $address = $socket->getLocalAddress();
            self::assertSame('127.0.0.1', $address->host);
            self::assertGreaterThan(0, $address->port);
            $socket->close();
        })->await();
    }

    public function testSendToActuallySendsData(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $target = $receiver->getLocalAddress();
            $bytes_sent = $sender->sendTo('test-data', $target);

            self::assertGreaterThan(0, $bytes_sent);
            self::assertSame(9, $bytes_sent);

            [$data] = $receiver->receiveFrom(1024);
            self::assertSame('test-data', $data);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testSendActuallySendsData(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);

            $server_addr = $server->getLocalAddress();
            $client->connect($server_addr->host, $server_addr->port);

            $bytes_sent = $client->send('connected-data');

            self::assertGreaterThan(0, $bytes_sent);
            self::assertSame(14, $bytes_sent);

            [$data] = $server->receiveFrom(1024);
            self::assertSame('connected-data', $data);

            $client->close();
            $server->close();
        })->await();
    }

    public function testPayloadExactlyAtMaxDatagramSizePassesValidation(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $data = str_repeat('x', 65_507);
            $threw_invalid_argument = false;
            try {
                $socket->sendTo($data, Network\Address::udp('127.0.0.1', 9999));
            } catch (Network\Exception\InvalidArgumentException) {
                $threw_invalid_argument = true;
            } catch (Network\Exception\RuntimeException) {
                // @mago-expect lint:no-empty-catch-clause
                // OS-level send failure is acceptable
            } finally {
                $socket->close();
            }

            self::assertFalse(
                $threw_invalid_argument,
                'Payload at exactly MAX_DATAGRAM_SIZE should not fail validation',
            );
        })->await();
    }

    public function testPayloadOneOverMaxDatagramSizeThrows(): void
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

    public function testPayloadSizeValidationOnSend(): void
    {
        $this->expectException(Network\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds maximum size');

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $client->send(str_repeat('x', 65_508));
            } finally {
                $client->close();
                $server->close();
            }
        })->await();
    }

    public function testGetStreamReturnsResource(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $stream = $socket->getStream();
            self::assertIsResource($stream);
            $socket->close();
        })->await();
    }

    public function testGetStreamReturnsNullAfterClose(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $stream = $socket->getStream();
            self::assertNull($stream);
        })->await();
    }

    public function testDoubleCloseDoesNotThrow(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->close();
            self::assertNull($socket->getStream());
        })->await();
    }

    public function testBindWithSpecificPort(): void
    {
        Async\run(static function (): void {
            $temp = UDP\Socket::bind('127.0.0.1', 0);
            $port = $temp->getLocalAddress()->port;
            $temp->close();

            $socket = UDP\Socket::bind('127.0.0.1', $port);
            $address = $socket->getLocalAddress();
            self::assertSame($port, $address->port);
            $socket->close();
        })->await();
    }

    public function testSendToWithTimeout(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $target = $receiver->getLocalAddress();
            $bytes_sent = $sender->sendTo('timeout-test', $target, Duration::seconds(5));

            self::assertGreaterThan(0, $bytes_sent);
            self::assertSame(12, $bytes_sent);

            [$data] = $receiver->receiveFrom(1024);
            self::assertSame('timeout-test', $data);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testSendWithTimeout(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);

            $bytes_sent = $client->send('timeout-send', Duration::seconds(5));

            self::assertGreaterThan(0, $bytes_sent);
            self::assertSame(12, $bytes_sent);

            [$data] = $server->receiveFrom(1024);
            self::assertSame('timeout-send', $data);

            $client->close();
            $server->close();
        })->await();
    }

    public function testReceiveTimeout(): void
    {
        $this->expectException(IO\Exception\TimeoutException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $client = UDP\Socket::bind('127.0.0.1', 0);
            $client->connect($server->getLocalAddress()->host, $server->getLocalAddress()->port);
            try {
                $client->receive(1024, Duration::milliseconds(50));
            } finally {
                $client->close();
                $server->close();
            }
        })->await();
    }
}
