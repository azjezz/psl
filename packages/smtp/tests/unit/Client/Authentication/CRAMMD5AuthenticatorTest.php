<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client\Authentication;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network\StreamInterface;
use Psl\SMTP\Client\Authentication\CRAMMD5Authenticator;
use Psl\SMTP\Client\Connection;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\Str\Byte;
use Psl\TCP;

use function base64_decode;
use function base64_encode;
use function hash_hmac;

final class CRAMMD5AuthenticatorTest extends TestCase
{
    public function testMechanism(): void
    {
        $auth = new CRAMMD5Authenticator('user', 'pass');

        static::assertSame('CRAM-MD5', $auth->mechanism);
    }

    public function testSuccessfulAuthentication(): void
    {
        [$connection, $serverStream] = $this->createPair('CRAM-MD5');

        $challenge = '<1234.5678@mail.example.com>';

        Async\run(static function () use ($serverStream, $challenge): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringContainsString('AUTH CRAM-MD5', $command);
            $serverStream->writeAll('334 ' . base64_encode($challenge) . "\r\n");

            $response = '';
            while (!Byte\contains($response, "\r\n")) {
                $response .= $serverStream->read();
            }

            $decoded = base64_decode(Byte\trim($response), true);
            $expectedDigest = hash_hmac('md5', $challenge, 'secret');
            static::assertSame('testuser ' . $expectedDigest, $decoded);

            $serverStream->writeAll("235 Authentication successful\r\n");
        });

        $auth = new CRAMMD5Authenticator('testuser', 'secret');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testAuthenticationRejected(): void
    {
        [$connection, $serverStream] = $this->createPair('CRAM-MD5');

        $challenge = '<test@example.com>';

        Async\run(static function () use ($serverStream, $challenge): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll('334 ' . base64_encode($challenge) . "\r\n");

            $response = '';
            while (!Byte\contains($response, "\r\n")) {
                $response .= $serverStream->read();
            }

            $serverStream->writeAll("535 Authentication failed\r\n");
        });

        $auth = new CRAMMD5Authenticator('user', 'wrong');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testAuthCommandRejected(): void
    {
        [$connection, $serverStream] = $this->createPair('CRAM-MD5');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("504 Mechanism not available\r\n");
        });

        $auth = new CRAMMD5Authenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testUnsupportedMechanism(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN LOGIN');

        $auth = new CRAMMD5Authenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testNoAuthCapability(): void
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

        $auth = new CRAMMD5Authenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testCramMd5AmongMultipleMechanisms(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN LOGIN CRAM-MD5');

        $challenge = '<multi@example.com>';

        Async\run(static function () use ($serverStream, $challenge): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringContainsString('AUTH CRAM-MD5', $command);
            $serverStream->writeAll('334 ' . base64_encode($challenge) . "\r\n");

            $response = '';
            while (!Byte\contains($response, "\r\n")) {
                $response .= $serverStream->read();
            }

            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new CRAMMD5Authenticator('user', 'pass');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testDigestIsCorrectHmacMd5(): void
    {
        [$connection, $serverStream] = $this->createPair('CRAM-MD5');

        $challenge = 'PDE4OTYuNjk3MTcwOTUyQHBvc3RvZmZpY2UucmVzdG9uLm1jaS5uZXQ+';
        $rawChallenge = base64_decode($challenge, true);

        Async\run(static function () use ($serverStream, $challenge, $rawChallenge): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll('334 ' . $challenge . "\r\n");

            $response = '';
            while (!Byte\contains($response, "\r\n")) {
                $response .= $serverStream->read();
            }

            $decoded = base64_decode(Byte\trim($response), true);
            $expectedDigest = hash_hmac('md5', $rawChallenge, 'tanstraafl');
            static::assertSame('tim ' . $expectedDigest, $decoded);

            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new CRAMMD5Authenticator('tim', 'tanstraafl');
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
