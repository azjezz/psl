<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client\Authentication;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network\StreamInterface;
use Psl\SMTP\Client\Authentication\LoginAuthenticator;
use Psl\SMTP\Client\Connection;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\Str\Byte;
use Psl\TCP;

use function base64_decode;

final class LoginAuthenticatorTest extends TestCase
{
    public function testMechanism(): void
    {
        $auth = new LoginAuthenticator('user', 'pass');

        static::assertSame('LOGIN', $auth->mechanism);
    }

    public function testSuccessfulAuthentication(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringContainsString('AUTH LOGIN', $command);
            $serverStream->writeAll("334 VXNlcm5hbWU6\r\n");

            $username = '';
            while (!Byte\contains($username, "\r\n")) {
                $username .= $serverStream->read();
            }

            static::assertSame('user', base64_decode(Byte\trim($username), true));
            $serverStream->writeAll("334 UGFzc3dvcmQ6\r\n");

            $password = '';
            while (!Byte\contains($password, "\r\n")) {
                $password .= $serverStream->read();
            }

            static::assertSame('pass', base64_decode(Byte\trim($password), true));
            $serverStream->writeAll("235 Authentication successful\r\n");
        });

        $auth = new LoginAuthenticator('user', 'pass');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testAuthenticationRejectedAtLogin(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("535 Authentication failed\r\n");
        });

        $auth = new LoginAuthenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testAuthenticationRejectedAtPassword(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("334 VXNlcm5hbWU6\r\n");

            $username = '';
            while (!Byte\contains($username, "\r\n")) {
                $username .= $serverStream->read();
            }

            $serverStream->writeAll("334 UGFzc3dvcmQ6\r\n");

            $password = '';
            while (!Byte\contains($password, "\r\n")) {
                $password .= $serverStream->read();
            }

            $serverStream->writeAll("535 Bad credentials\r\n");
        });

        $auth = new LoginAuthenticator('user', 'wrong');

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
        [$connection, $serverStream] = $this->createPair('PLAIN');

        $auth = new LoginAuthenticator('user', 'pass');

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

            $serverStream->writeAll("250-mail.example.com\r\n250 8BITMIME\r\n");
        });

        /** @var Connection $connection */
        $connection = new Connection($clientStream);
        $connection->ehlo('test');

        $auth = new LoginAuthenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testAuthenticationRejectedAtUsernameStep(): void
    {
        [$connection, $serverStream] = $this->createPair('LOGIN');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            // Respond with 334 for username prompt
            $serverStream->writeAll("334 VXNlcm5hbWU6\r\n");

            $username = '';
            while (!Byte\contains($username, "\r\n")) {
                $username .= $serverStream->read();
            }

            // Reject at username step
            $serverStream->writeAll("535 Invalid username\r\n");
        });

        $auth = new LoginAuthenticator('baduser', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testLoginAmongMultipleMechanisms(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN LOGIN XOAUTH2');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringContainsString('AUTH LOGIN', $command);
            $serverStream->writeAll("334 VXNlcm5hbWU6\r\n");

            $username = '';
            while (!Byte\contains($username, "\r\n")) {
                $username .= $serverStream->read();
            }

            $serverStream->writeAll("334 UGFzc3dvcmQ6\r\n");

            $password = '';
            while (!Byte\contains($password, "\r\n")) {
                $password .= $serverStream->read();
            }

            $serverStream->writeAll("235 OK\r\n");
        });

        $auth = new LoginAuthenticator('user', 'pass');
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
