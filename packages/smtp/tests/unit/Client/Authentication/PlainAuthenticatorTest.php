<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client\Authentication;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network\StreamInterface;
use Psl\SMTP\Client\Authentication\PlainAuthenticator;
use Psl\SMTP\Client\Connection;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\Str\Byte;
use Psl\TCP;

use function base64_decode;

final class PlainAuthenticatorTest extends TestCase
{
    public function testMechanism(): void
    {
        $auth = new PlainAuthenticator('user', 'pass');

        static::assertSame('PLAIN', $auth->mechanism);
    }

    public function testSuccessfulAuthentication(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringStartsWith('AUTH PLAIN ', Byte\trim($command));

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $decoded = base64_decode($parts[2], true);
            static::assertSame("\0user\0pass", $decoded);

            $serverStream->writeAll("235 Authentication successful\r\n");
        });

        $auth = new PlainAuthenticator('user', 'pass');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testAuthenticationRejected(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("535 Authentication failed\r\n");
        });

        $auth = new PlainAuthenticator('user', 'wrong');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testWithAuthorizationIdentity(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $decoded = base64_decode($parts[2], true);
            static::assertSame("admin\0user\0pass", $decoded);

            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new PlainAuthenticator('user', 'pass', 'admin');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testUnsupportedMechanism(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN');

        $auth = new PlainAuthenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testNoAuthCapabilityAtAll(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        $clientStream = null;
        $serverStream = null;

        Async\concurrently([
            static function () use ($server, &$serverStream): void {
                $serverStream = $server->accept();
            },
            static function () use ($address, &$clientStream): void {
                $clientStream = TCP\connect($address->host, $address->port);
            },
        ]);

        $server->close();

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("250-mail.example.com\r\n250 PIPELINING\r\n");
        });

        /** @var Connection $connection */
        $connection = new Connection($clientStream);
        $connection->ehlo('test');

        $auth = new PlainAuthenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testPlainAmongMultipleMechanisms(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN PLAIN XOAUTH2');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringStartsWith('AUTH PLAIN ', Byte\trim($command));
            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new PlainAuthenticator('user', 'pass');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testEmptyAuthorizationIdentity(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $decoded = base64_decode($parts[2], true);
            static::assertSame("\0user\0pass", $decoded);

            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new PlainAuthenticator('user', 'pass', '');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    /**
     * @return array{Connection, StreamInterface}
     *
     * @mago-expect analysis:possibly-null-argument
     * @mago-expect analysis:invalid-return-statement
     */
    private function createPair(string $authMechanisms): array
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        $clientStream = null;
        $serverStream = null;

        Async\concurrently([
            static function () use ($server, &$serverStream): void {
                $serverStream = $server->accept();
            },
            static function () use ($address, &$clientStream): void {
                $clientStream = TCP\connect($address->host, $address->port);
            },
        ]);

        $server->close();

        Async\run(static function () use ($serverStream, $authMechanisms): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("250-mail.example.com\r\n250 AUTH " . $authMechanisms . "\r\n");
        });

        $connection = new Connection($clientStream);
        $connection->ehlo('test');

        return [$connection, $serverStream];
    }
}
