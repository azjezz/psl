<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client\Authentication;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network\StreamInterface;
use Psl\SMTP\Client\Authentication\SCRAMSHA256Authenticator;
use Psl\SMTP\Client\Connection;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\Str\Byte;
use Psl\TCP;

use function base64_decode;
use function base64_encode;
use function hash_hmac;
use function hash_pbkdf2;
use function preg_match;
use function random_bytes;

final class SCRAMSHA256AuthenticatorTest extends TestCase
{
    public function testMechanism(): void
    {
        $auth = new SCRAMSHA256Authenticator('user', 'pass');

        static::assertSame('SCRAM-SHA-256', $auth->mechanism);
    }

    public function testSuccessfulAuthentication(): void
    {
        [$connection, $serverStream] = $this->createPair('SCRAM-SHA-256');

        // @mago-expect lint:no-literal-password
        $password = 'pencil';
        $salt = random_bytes(16);
        $iterations = 4096;

        Async\run(static function () use ($serverStream, $password, $salt, $iterations): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringStartsWith('AUTH SCRAM-SHA-256 ', Byte\trim($command));

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $clientFirstMessage = base64_decode($parts[2], true);
            static::assertNotFalse($clientFirstMessage);
            static::assertStringStartsWith('n,,', $clientFirstMessage);

            $clientFirstBare = Byte\slice($clientFirstMessage, 3);
            preg_match('/r=([^,]+)/', $clientFirstBare, $matches);
            $clientNonce = $matches[1];

            $serverNonce = $clientNonce . 'server-nonce-extension';
            $serverFirstMessage = 'r=' . $serverNonce . ',s=' . base64_encode($salt) . ',i=' . $iterations;

            $serverStream->writeAll('334 ' . base64_encode($serverFirstMessage) . "\r\n");

            $clientFinal = '';
            while (!Byte\contains($clientFinal, "\r\n")) {
                $clientFinal .= $serverStream->read();
            }

            $clientFinalMessage = base64_decode(Byte\trim($clientFinal), true);
            static::assertNotFalse($clientFinalMessage);

            preg_match('/p=([^,]+)/', $clientFinalMessage, $proofMatch);
            static::assertNotEmpty($proofMatch);

            $saltedPassword = hash_pbkdf2('sha256', $password, $salt, $iterations, 0, true);
            $serverKey = hash_hmac('sha256', 'Server Key', $saltedPassword, true);

            $channelBinding = base64_encode('n,,');
            $clientFinalWithoutProof = 'c=' . $channelBinding . ',r=' . $serverNonce;
            $authMessage = $clientFirstBare . ',' . $serverFirstMessage . ',' . $clientFinalWithoutProof;

            $serverSignature = hash_hmac('sha256', $authMessage, $serverKey, true);

            $serverFinalMessage = 'v=' . base64_encode($serverSignature);
            $serverStream->writeAll('235 ' . base64_encode($serverFinalMessage) . "\r\n");
        });

        $auth = new SCRAMSHA256Authenticator('user', $password);
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testAuthCommandRejected(): void
    {
        [$connection, $serverStream] = $this->createPair('SCRAM-SHA-256');

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $serverStream->writeAll("504 Mechanism not available\r\n");
        });

        $auth = new SCRAMSHA256Authenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testServerRejectsProof(): void
    {
        [$connection, $serverStream] = $this->createPair('SCRAM-SHA-256');

        $salt = random_bytes(16);

        Async\run(static function () use ($serverStream, $salt): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $clientFirstMessage = base64_decode($parts[2], true);
            $clientFirstBare = Byte\slice($clientFirstMessage, 3);
            preg_match('/r=([^,]+)/', $clientFirstBare, $matches);
            $clientNonce = $matches[1];

            $serverNonce = $clientNonce . 'ext';
            $serverFirstMessage = 'r=' . $serverNonce . ',s=' . base64_encode($salt) . ',i=4096';

            $serverStream->writeAll('334 ' . base64_encode($serverFirstMessage) . "\r\n");

            $clientFinal = '';
            while (!Byte\contains($clientFinal, "\r\n")) {
                $clientFinal .= $serverStream->read();
            }

            $serverStream->writeAll("535 Authentication failed\r\n");
        });

        $auth = new SCRAMSHA256Authenticator('user', 'wrong');

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

        $auth = new SCRAMSHA256Authenticator('user', 'pass');

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

        $auth = new SCRAMSHA256Authenticator('user', 'pass');

        $this->expectException(AuthenticationException::class);

        try {
            $auth->authenticate($connection);
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    public function testScramAmongMultipleMechanisms(): void
    {
        [$connection, $serverStream] = $this->createPair('PLAIN LOGIN SCRAM-SHA-256');

        $salt = random_bytes(16);

        Async\run(static function () use ($serverStream, $salt): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringStartsWith('AUTH SCRAM-SHA-256 ', Byte\trim($command));

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $clientFirstMessage = base64_decode($parts[2], true);
            $clientFirstBare = Byte\slice($clientFirstMessage, 3);
            preg_match('/r=([^,]+)/', $clientFirstBare, $matches);
            $clientNonce = $matches[1];

            $serverNonce = $clientNonce . 'srv';
            $serverFirstMessage = 'r=' . $serverNonce . ',s=' . base64_encode($salt) . ',i=4096';
            $serverStream->writeAll('334 ' . base64_encode($serverFirstMessage) . "\r\n");

            $clientFinal = '';
            while (!Byte\contains($clientFinal, "\r\n")) {
                $clientFinal .= $serverStream->read();
            }

            $saltedPassword = hash_pbkdf2('sha256', 'pass', $salt, 4096, 0, true);
            $serverKey = hash_hmac('sha256', 'Server Key', $saltedPassword, true);

            $channelBinding = base64_encode('n,,');
            $clientFinalWithoutProof = 'c=' . $channelBinding . ',r=' . $serverNonce;
            $authMessage = $clientFirstBare . ',' . $serverFirstMessage . ',' . $clientFinalWithoutProof;
            $serverSignature = hash_hmac('sha256', $authMessage, $serverKey, true);

            $serverStream->writeAll('235 ' . base64_encode('v=' . base64_encode($serverSignature)) . "\r\n");
        });

        $auth = new SCRAMSHA256Authenticator('user', 'pass');
        $auth->authenticate($connection);

        $connection->close();
        $serverStream->close();
    }

    public function testUsernameEscaping(): void
    {
        [$connection, $serverStream] = $this->createPair('SCRAM-SHA-256');

        $salt = random_bytes(16);

        Async\run(static function () use ($serverStream, $salt): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            $parts = Byte\split(Byte\trim($command), ' ', 3);
            $clientFirstMessage = base64_decode($parts[2], true);
            $clientFirstBare = Byte\slice($clientFirstMessage, 3);

            static::assertStringContainsString('n=user=3Dname=2Cwith', $clientFirstBare);

            preg_match('/,r=([^,]+)/', $clientFirstBare, $matches);
            $clientNonce = $matches[1];

            $serverNonce = $clientNonce . 'ext';
            $serverFirstMessage = 'r=' . $serverNonce . ',s=' . base64_encode($salt) . ',i=4096';
            $serverStream->writeAll('334 ' . base64_encode($serverFirstMessage) . "\r\n");

            $clientFinal = '';
            while (!Byte\contains($clientFinal, "\r\n")) {
                $clientFinal .= $serverStream->read();
            }

            $saltedPassword = hash_pbkdf2('sha256', 'pass', $salt, 4096, 0, true);
            $serverKey = hash_hmac('sha256', 'Server Key', $saltedPassword, true);

            $channelBinding = base64_encode('n,,');
            $clientFinalWithoutProof = 'c=' . $channelBinding . ',r=' . $serverNonce;
            $authMessage = $clientFirstBare . ',' . $serverFirstMessage . ',' . $clientFinalWithoutProof;
            $serverSignature = hash_hmac('sha256', $authMessage, $serverKey, true);

            $serverStream->writeAll('235 ' . base64_encode('v=' . base64_encode($serverSignature)) . "\r\n");
        });

        $auth = new SCRAMSHA256Authenticator('user=name,with', 'pass');
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
