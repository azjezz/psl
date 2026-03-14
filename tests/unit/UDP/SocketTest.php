<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\UDP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Network;
use Psl\OS;
use Psl\UDP;

final class SocketTest extends TestCase
{
    public function testBindAndGetLocalAddress(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $address = $socket->getLocalAddress();
            static::assertSame('127.0.0.1', $address->host);
            static::assertGreaterThan(0, $address->port);
            static::assertSame(Network\SocketScheme::Udp, $address->scheme);
            $socket->close();
        })->await();
    }

    public function testBindWithOptions(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0, false, false, false);
            $address = $socket->getLocalAddress();
            static::assertSame('127.0.0.1', $address->host);
            static::assertGreaterThan(0, $address->port);
            $socket->close();
        })->await();
    }

    public function testBindWithSpecificPort(): void
    {
        Async\run(static function (): void {
            $temp = UDP\Socket::bind('127.0.0.1', 0);
            $port = $temp->getLocalAddress()->port;
            $temp->close();

            $socket = UDP\Socket::bind('127.0.0.1', $port);
            static::assertSame($port, $socket->getLocalAddress()->port);
            $socket->close();
        })->await();
    }

    public function testBindFailsWithInvalidAddress(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);

        UDP\Socket::bind('999.999.999.999', 0);
    }

    public function testSendToAndReceiveFrom(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $sender->sendTo('hello', $receiver->getLocalAddress());

            [$data, $from] = $receiver->receiveFrom(1024);
            static::assertSame('hello', $data);
            static::assertSame('127.0.0.1', $from->host);
            static::assertSame($sender->getLocalAddress()->port, $from->port);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testSendToReturnsByteCount(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $bytes_sent = $sender->sendTo('test-data', $receiver->getLocalAddress());
            static::assertSame(9, $bytes_sent);

            [$data] = $receiver->receiveFrom(1024);
            static::assertSame('test-data', $data);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testPeekFromDoesNotConsumeData(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $sender->sendTo('peek-test', $receiver->getLocalAddress());

            [$data, $from] = $receiver->peekFrom(1024);
            static::assertSame('peek-test', $data);
            static::assertSame('127.0.0.1', $from->host);
            static::assertSame($sender->getLocalAddress()->port, $from->port);

            [$data2] = $receiver->receiveFrom(1024);
            static::assertSame('peek-test', $data2);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testSendToWithTimeout(): void
    {
        Async\run(static function (): void {
            $receiver = UDP\Socket::bind('127.0.0.1', 0);
            $sender = UDP\Socket::bind('127.0.0.1', 0);

            $bytes_sent = $sender->sendTo(
                'timeout-test',
                $receiver->getLocalAddress(),
                new Async\TimeoutCancellationToken(Duration::seconds(5)),
            );
            static::assertSame(12, $bytes_sent);

            [$data] = $receiver->receiveFrom(1024);
            static::assertSame('timeout-test', $data);

            $sender->close();
            $receiver->close();
        })->await();
    }

    public function testReceiveFromTimeout(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->receiveFrom(1024, new Async\TimeoutCancellationToken(Duration::milliseconds(50)));
            } finally {
                $socket->close();
            }
        })->await();
    }

    public function testPeekFromTimeout(): void
    {
        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            try {
                $socket->peekFrom(1024, new Async\TimeoutCancellationToken(Duration::milliseconds(50)));
            } finally {
                $socket->close();
            }
        })->await();
    }

    public function testSendToOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->sendTo('data', Network\Address::udp('127.0.0.1', 9999));
        })->await();
    }

    public function testReceiveFromOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->receiveFrom(1024);
        })->await();
    }

    public function testPeekFromOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->peekFrom(1024);
        })->await();
    }

    public function testConnectOnClosedSocketThrows(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->connect('127.0.0.1', 9999);
        })->await();
    }

    public function testPayloadSizeValidationOnSendTo(): void
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

    public function testPayloadExactlyAtMaxDoesNotThrowValidation(): void
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
            } finally {
                $socket->close();
            }

            static::assertFalse($threw_invalid_argument);
        })->await();
    }

    public function testConnectReturnsConnectedSocket(): void
    {
        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $server_addr = $server->getLocalAddress();

            $connected = $socket->connect($server_addr->host, $server_addr->port);

            static::assertInstanceOf(UDP\ConnectedSocket::class, $connected);
            static::assertSame($server_addr->host, $connected->getPeerAddress()->host);
            static::assertSame($server_addr->port, $connected->getPeerAddress()->port);

            $connected->close();
            $server->close();
        })->await();
    }

    public function testConnectInvalidatesOriginalSocket(): void
    {
        $this->expectException(IO\Exception\AlreadyClosedException::class);

        Async\run(static function (): void {
            $server = UDP\Socket::bind('127.0.0.1', 0);
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $server_addr = $server->getLocalAddress();

            $connected = $socket->connect($server_addr->host, $server_addr->port);

            try {
                $socket->getLocalAddress();
            } finally {
                $connected->close();
                $server->close();
            }
        })->await();
    }

    public function testGetStreamReturnsResource(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            static::assertIsResource($socket->getStream());
            $socket->close();
        })->await();
    }

    public function testGetStreamReturnsNullAfterClose(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            static::assertNull($socket->getStream());
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

    public function testDoubleCloseDoesNotThrow(): void
    {
        Async\run(static function (): void {
            $socket = UDP\Socket::bind('127.0.0.1', 0);
            $socket->close();
            $socket->close();
            static::assertNull($socket->getStream());
        })->await();
    }

    public function testParseAddressEmptyString(): void
    {
        $result = UDP\Internal\parse_address('');
        static::assertSame('0.0.0.0', $result->host);
        static::assertSame(0, $result->port);
    }

    public function testParseAddressIpv6WithPort(): void
    {
        $result = UDP\Internal\parse_address('[::1]:8080');
        static::assertSame('::1', $result->host);
        static::assertSame(8080, $result->port);
    }

    public function testParseAddressIpv6WithoutPort(): void
    {
        $result = UDP\Internal\parse_address('[::1]');
        static::assertSame('::1', $result->host);
        static::assertSame(0, $result->port);
    }

    public function testParseAddressIpv6EmptyHost(): void
    {
        $result = UDP\Internal\parse_address('[]:8080');
        static::assertSame('::', $result->host);
        static::assertSame(8080, $result->port);
    }

    public function testParseAddressIpv6NoBracketClose(): void
    {
        $result = UDP\Internal\parse_address('[::1');
        static::assertSame('[::1', $result->host);
        static::assertSame(0, $result->port);
    }

    public function testParseAddressIpv4WithPort(): void
    {
        $result = UDP\Internal\parse_address('127.0.0.1:8080');
        static::assertSame('127.0.0.1', $result->host);
        static::assertSame(8080, $result->port);
    }

    public function testParseAddressIpv4WithoutColon(): void
    {
        $result = UDP\Internal\parse_address('192.168.1.1');
        static::assertSame('192.168.1.1', $result->host);
        static::assertSame(0, $result->port);
    }

    public function testParseAddressEmptyHostWithPort(): void
    {
        $result = UDP\Internal\parse_address(':8080');
        static::assertSame('0.0.0.0', $result->host);
        static::assertSame(8080, $result->port);
    }

    public function testParseAddressIpv6WithEmptyPort(): void
    {
        $result = UDP\Internal\parse_address('[::1]:');
        static::assertSame('::1', $result->host);
        static::assertSame(0, $result->port);
    }

    public function testParseAddressIpv4InvalidPort(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid port number');

        UDP\Internal\parse_address('127.0.0.1:99999');
    }

    public function testParseAddressIpv6InvalidPort(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid port number');

        UDP\Internal\parse_address('[::1]:99999');
    }

    public function testWaitWritableTimeout(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('stream_socket_pair with STREAM_PF_UNIX not available on Windows');
        }

        $this->expectException(Async\Exception\CancelledException::class);

        Async\run(static function (): void {
            $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            stream_set_blocking($pair[0], false);
            stream_set_blocking($pair[1], false);

            $chunk = str_repeat('x', 65_536);
            while (@fwrite($pair[0], $chunk) > 0) {
                // @mago-expect lint:no-empty-loop
            }

            try {
                UDP\Internal\wait_writable($pair[0], new Async\TimeoutCancellationToken(Duration::milliseconds(50)));
            } finally {
                fclose($pair[0]);
                fclose($pair[1]);
            }
        })->await();
    }
}
