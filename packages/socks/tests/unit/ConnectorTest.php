<?php

declare(strict_types=1);

namespace Psl\Socks\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Socks;
use Psl\TCP;

use function ord;
use function unpack;

final class ConnectorTest extends TestCase
{
    public function testConnectNoAuth(): void
    {
        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_100);
                $client = $listener->accept();

                $greeting = $client->readFixedSize(3);
                self::assertSame("\x05\x01\x00", $greeting);

                $client->writeAll("\x05\x00");

                $header = $client->readFixedSize(4);
                self::assertSame("\x05", $header[0]);
                self::assertSame("\x01", $header[1]);
                self::assertSame("\x00", $header[2]);
                self::assertSame("\x03", $header[3]);

                $domainLen = ord($client->readFixedSize(1));
                $domain = $client->readFixedSize($domainLen);
                self::assertSame('target.local', $domain);

                $portBytes = $client->readFixedSize(2);
                $port = unpack('n', $portBytes)[1];
                self::assertSame(8080, $port);

                $client->writeAll("\x05\x00\x00\x01\x00\x00\x00\x00\x00\x00");

                $data = $client->read();
                self::assertSame('hello-proxy', $data);
                $client->writeAll('proxy-reply');
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('127.0.0.1', 18_100));
                $stream = $connector->connect('target.local', 8080);
                $stream->writeAll('hello-proxy');
                $response = $stream->readAll();
                self::assertSame('proxy-reply', $response);
                $stream->close();
            },
        ]);
    }

    public function testConnectWithUsernamePasswordAuth(): void
    {
        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_101);
                $client = $listener->accept();

                $greeting = $client->readFixedSize(4);
                self::assertSame("\x05\x02\x00\x02", $greeting);

                $client->writeAll("\x05\x02");

                $authVersion = $client->readFixedSize(1);
                self::assertSame("\x01", $authVersion);

                $usernameLen = ord($client->readFixedSize(1));
                $username = $client->readFixedSize($usernameLen);
                self::assertSame('user', $username);

                $passwordLen = ord($client->readFixedSize(1));
                $password = $client->readFixedSize($passwordLen);
                self::assertSame('pass', $password);

                $client->writeAll("\x01\x00");

                $header = $client->readFixedSize(4);
                self::assertSame("\x05\x01\x00\x01", $header);

                $client->readFixedSize(4);
                $client->readFixedSize(2);

                $client->writeAll("\x05\x00\x00\x01\x00\x00\x00\x00\x00\x00");

                $data = $client->read();
                self::assertSame('auth-test', $data);
                $client->writeAll('auth-ok');
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(
                    new TCP\Connector(),
                    new Socks\Configuration('127.0.0.1', 18_101, 'user', 'pass'),
                );
                $stream = $connector->connect('127.0.0.1', 9999);
                $stream->writeAll('auth-test');
                $response = $stream->readAll();
                self::assertSame('auth-ok', $response);
                $stream->close();
            },
        ]);
    }

    public function testConnectAuthFailure(): void
    {
        $this->expectException(Socks\Exception\AuthenticationException::class);
        $this->expectExceptionMessage('invalid credentials');

        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_102);
                $client = $listener->accept();

                $client->readFixedSize(4);
                $client->writeAll("\x05\x02");

                $client->readFixedSize(1);
                $usernameLen = ord($client->readFixedSize(1));
                $client->readFixedSize($usernameLen);
                $passwordLen = ord($client->readFixedSize(1));
                $client->readFixedSize($passwordLen);

                $client->writeAll("\x01\x01");
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(
                    new TCP\Connector(),
                    new Socks\Configuration('127.0.0.1', 18_102, 'bad', 'creds'),
                );
                $connector->connect('example.com', 80);
            },
        ]);
    }

    public function testConnectConnectionRefused(): void
    {
        $this->expectException(Socks\Exception\SocksException::class);
        $this->expectExceptionMessage('connection refused');

        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_103);
                $client = $listener->accept();

                $client->readFixedSize(3);
                $client->writeAll("\x05\x00");

                $header = $client->readFixedSize(4);
                $domainLen = ord($client->readFixedSize(1));
                $client->readFixedSize($domainLen);
                $client->readFixedSize(2);

                $client->writeAll("\x05\x05\x00\x01\x00\x00\x00\x00\x00\x00");
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('127.0.0.1', 18_103));
                $connector->connect('unreachable.local', 80);
            },
        ]);
    }

    public function testConnectWithIPv6Target(): void
    {
        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_104);
                $client = $listener->accept();

                $client->readFixedSize(3);
                $client->writeAll("\x05\x00");

                $header = $client->readFixedSize(4);
                self::assertSame("\x04", $header[3]);

                $client->readFixedSize(16);
                $client->readFixedSize(2);

                $client->writeAll("\x05\x00\x00\x01\x00\x00\x00\x00\x00\x00");

                $data = $client->read();
                self::assertSame('ipv6-test', $data);
                $client->writeAll('ipv6-ok');
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('127.0.0.1', 18_104));
                $stream = $connector->connect('::1', 8080);
                $stream->writeAll('ipv6-test');
                $response = $stream->readAll();
                self::assertSame('ipv6-ok', $response);
                $stream->close();
            },
        ]);
    }

    public function testConnectorUsesProvidedConnector(): void
    {
        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_105);
                $client = $listener->accept();

                $client->readFixedSize(3);
                $client->writeAll("\x05\x00");

                $header = $client->readFixedSize(4);
                $domainLen = ord($client->readFixedSize(1));
                $client->readFixedSize($domainLen);
                $client->readFixedSize(2);

                $client->writeAll("\x05\x00\x00\x01\x00\x00\x00\x00\x00\x00");

                $data = $client->read();
                self::assertSame('custom-connector', $data);
                $client->writeAll('ok');
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $innerConnector = new TCP\StaticConnector('127.0.0.1', 18_105);
                $connector = new Socks\Connector($innerConnector, new Socks\Configuration('ignored', 0));
                $stream = $connector->connect('target.local', 80);
                $stream->writeAll('custom-connector');
                $response = $stream->readAll();
                self::assertSame('ok', $response);
                $stream->close();
            },
        ]);
    }

    public function testRejectedAuthMethod(): void
    {
        $this->expectException(Socks\Exception\SocksException::class);
        $this->expectExceptionMessage('rejected all offered authentication methods');

        Async\concurrently::<string, void>([
            'proxy' => static function (): void {
                $listener = TCP\listen('127.0.0.1', 18_106);
                $client = $listener->accept();

                $client->readFixedSize(3);
                $client->writeAll("\x05\xFF");
                $client->close();
                $listener->close();
            },
            'client' => static function (): void {
                $connector = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('127.0.0.1', 18_106));
                $connector->connect('example.com', 80);
            },
        ]);
    }
}
