<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\IO;
use Psl\Iter;
use Psl\Message\Address\Mailbox;
use Psl\Message\Envelope;
use Psl\Message\Message;
use Psl\MIME\Part;
use Psl\Network;
use Psl\SMTP\Client\Authentication\PlainAuthenticator;
use Psl\SMTP\Client\SendConfiguration;
use Psl\SMTP\Client\Transport;
use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Exception\TransmissionException;
use Psl\SMTP\Security;
use Psl\Str\Byte;
use Psl\TCP;

use function base64_decode;

use const PHP_OS_FAMILY;

/**
 * @mago-expect lint:kan-defect
 */
final class TransportTest extends TestCase
{
    protected function setUp(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('SMTP transport tests are not supported on Windows.');
        }
    }

    public function testSendOverPlainConnection(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            $ehlo = self::readCommand($client);
            static::assertStringContainsString('EHLO', $ehlo);
            $client->writeAll("250-mail.example.com\r\n250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            $rset = self::readCommand($client);
            static::assertStringContainsString('RSET', $rset);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello World')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithAuthentication(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 AUTH PLAIN LOGIN\r\n");

            $authCommand = self::readCommand($client);
            static::assertStringStartsWith('AUTH PLAIN ', Byte\trim($authCommand));

            $parts = Byte\split(Byte\trim($authCommand), ' ', 3);
            $decoded = base64_decode($parts[2], true);
            static::assertSame("\0testuser\0testpass", $decoded);

            $client->writeAll("235 Authentication successful\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $authenticator = new PlainAuthenticator('testuser', 'testpass');
        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration, $authenticator);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Auth Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testMultipleSendsReuseConnection(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        $ehloCount = 0;

        Async\run(static function () use ($server, &$ehloCount): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            $ehlo = self::readCommand($client);
            if (Byte\contains($ehlo, 'EHLO')) {
                $ehloCount++;
            }

            $client->writeAll("250 mail.example.com\r\n");

            for ($i = 0; $i < 2; $i++) {
                self::readCommand($client);
                $client->writeAll("250 OK\r\n");

                self::readCommand($client);
                $client->writeAll("250 OK\r\n");

                self::readCommand($client);
                $client->writeAll("354 Go ahead\r\n");

                $data = '';
                while (!Byte\contains($data, "\r\n.\r\n")) {
                    $data .= $client->read();
                }

                $client->writeAll("250 OK\r\n");

                self::readCommand($client);
                $client->writeAll("250 OK\r\n");
            }

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');

        for ($i = 0; $i < 2; $i++) {
            $envelope = new Envelope($sender, [$recipient]);
            $message = new Message()
                ->withFrom($sender)
                ->withTo($recipient)
                ->withSubject('Message ' . $i)
                ->withContent(new Part\Text(new IO\MemoryHandle('Hello ' . $i)));

            $transport->send($envelope, $message);
        }

        $transport->close();
        $server->close();

        static::assertSame(1, $ehloCount);
    }

    public function testConnectionRefused(): void
    {
        $configuration = new TransportConfiguration(host: '127.0.0.1', port: 1, security: Security::None);
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello World')));

        $this->expectException(ExceptionInterface::class);

        $transport->send($envelope, $message);
    }

    public function testBadGreeting(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();
            $client->writeAll("554 Service unavailable\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ConnectionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testEhloRejected(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();
            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("502 EHLO not supported\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ProtocolException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testAuthenticationFailedClearsPooledConnection(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        $connectCount = 0;

        Async\run(static function () use ($server, &$connectCount): void {
            for ($i = 0; $i < 2; $i++) {
                $client = $server->accept();
                $connectCount++;

                $client->writeAll("220 mail.example.com ESMTP\r\n");

                self::readCommand($client);
                $client->writeAll("250-mail.example.com\r\n250 AUTH PLAIN\r\n");

                self::readCommand($client);
                $client->writeAll("535 Authentication failed\r\n");
                $client->close();
            }
        });

        $authenticator = new PlainAuthenticator('user', 'wrong');
        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration, $authenticator);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        try {
            $transport->send($envelope, $message);
            static::fail('Expected AuthenticationException');
        } catch (AuthenticationException) {
            $this->addToAssertionCount(1);
        }

        try {
            $transport->send($envelope, $message);
            static::fail('Expected AuthenticationException');
        } catch (AuthenticationException) {
            $this->addToAssertionCount(1);
        }

        $transport->close();
        $server->close();

        static::assertSame(2, $connectCount);
    }

    public function testTransmissionErrorClearsPooledConnection(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        $connectCount = 0;

        Async\run(static function () use ($server, &$connectCount): void {
            for ($i = 0; $i < 2; $i++) {
                $client = $server->accept();
                $connectCount++;

                $client->writeAll("220 mail.example.com ESMTP\r\n");

                self::readCommand($client);
                $client->writeAll("250 mail.example.com\r\n");

                self::readCommand($client);
                $client->writeAll("550 Sender rejected\r\n");
                $client->close();
            }
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        try {
            $transport->send($envelope, $message);
            static::fail('Expected TransmissionException');
        } catch (TransmissionException) {
            $this->addToAssertionCount(1);
        }

        try {
            $transport->send($envelope, $message);
            static::fail('Expected TransmissionException');
        } catch (TransmissionException) {
            $this->addToAssertionCount(1);
        }

        $transport->close();
        $server->close();

        static::assertSame(2, $connectCount);
    }

    public function testMalformedGreeting(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();
            $client->writeAll("GARBAGE\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ExceptionInterface::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testServerClosesConnectionImmediately(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ExceptionInterface::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testSendWithPipelining(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 PIPELINING\r\n");

            $pipelined = '';
            $commandCount = 0;
            while ($commandCount < 3) {
                $pipelined .= $client->read();
                $commandCount = Iter\count(Byte\split($pipelined, "\r\n")) - 1;
            }

            static::assertStringContainsString('MAIL FROM:<sender@example.com>', $pipelined);
            static::assertStringContainsString('RCPT TO:<recipient@example.com>', $pipelined);
            static::assertStringContainsString('DATA', $pipelined);

            $client->writeAll("250 OK\r\n");
            $client->writeAll("250 OK\r\n");
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Pipeline Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithDSN(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 DSN\r\n");

            $mailFrom = self::readCommand($client);
            static::assertStringContainsString('RET=FULL', $mailFrom);
            $client->writeAll("250 OK\r\n");

            $rcptTo = self::readCommand($client);
            static::assertStringContainsString('NOTIFY=SUCCESS', $rcptTo);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('DSN Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $config = new SendConfiguration(dsnReturn: 'FULL', dsnNotify: 'SUCCESS');
        $transport->send($envelope, $message, $config);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithMultipleRecipients(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            // MAIL FROM
            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            // RCPT TO #1
            $rcpt1 = self::readCommand($client);
            static::assertStringContainsString('RCPT TO:<alice@example.com>', $rcpt1);
            $client->writeAll("250 OK\r\n");

            // RCPT TO #2
            $rcpt2 = self::readCommand($client);
            static::assertStringContainsString('RCPT TO:<bob@example.com>', $rcpt2);
            $client->writeAll("250 OK\r\n");

            // RCPT TO #3
            $rcpt3 = self::readCommand($client);
            static::assertStringContainsString('RCPT TO:<carol@example.com>', $rcpt3);
            $client->writeAll("250 OK\r\n");

            // DATA
            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipients = [
            new Mailbox('alice', 'example.com'),
            new Mailbox('bob', 'example.com'),
            new Mailbox('carol', 'example.com'),
        ];
        $envelope = new Envelope($sender, $recipients);

        $message = new Message()
            ->withFrom($sender)
            ->withTo(...$recipients)
            ->withSubject('Multi-recipient Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSenderRejectedThrowsTransmissionException(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            self::readCommand($client);
            $client->writeAll("550 Sender rejected\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testRecipientRejectedThrowsTransmissionException(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            // MAIL FROM - accept
            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            // RCPT TO - reject
            self::readCommand($client);
            $client->writeAll("550 User not found\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('unknown', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testDataRejectedThrowsTransmissionException(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            // DATA - reject with non-354
            self::readCommand($client);
            $client->writeAll("452 Insufficient storage\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testFinalDataResponseRejected(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            // Reject after data sent
            $client->writeAll("554 Message rejected\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testEhloFallbackToHelo(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com SMTP\r\n");

            // EHLO rejected
            $ehlo = self::readCommand($client);
            static::assertStringContainsString('EHLO', $ehlo);
            $client->writeAll("502 EHLO not implemented\r\n");

            // HELO succeeds
            $helo = self::readCommand($client);
            static::assertStringContainsString('HELO', $helo);
            $client->writeAll("250 mail.example.com\r\n");

            // MAIL FROM
            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            // RCPT TO
            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            // DATA
            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('HELO Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWith8BitMIME(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 8BITMIME\r\n");

            $mailFrom = self::readCommand($client);
            static::assertStringContainsString('BODY=8BITMIME', $mailFrom);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('8BITMIME Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithDSNEnvelopeId(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 OK\r\n");

            $mailFrom = self::readCommand($client);
            static::assertStringContainsString('ENVID=msg-001', $mailFrom);
            static::assertStringContainsString('RET=HDRS', $mailFrom);
            $client->writeAll("250 OK\r\n");

            $rcptTo = self::readCommand($client);
            static::assertStringContainsString('NOTIFY=FAILURE,DELAY', $rcptTo);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('DSN ENVID Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $config = new SendConfiguration(dsnReturn: 'HDRS', dsnEnvelopeId: 'msg-001', dsnNotify: 'FAILURE,DELAY');
        $transport->send($envelope, $message, $config);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithNullSender(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            $mailFrom = self::readCommand($client);
            static::assertStringContainsString('MAIL FROM:<>', $mailFrom);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope(null, [$recipient]);

        $message = new Message()
            ->withTo($recipient)
            ->withSubject('Bounce')
            ->withContent(new Part\Text(new IO\MemoryHandle('Bounce message')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testPipeliningSenderRejected(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 PIPELINING\r\n");

            // Read pipelined commands
            $pipelined = '';
            $commandCount = 0;
            while ($commandCount < 3) {
                $pipelined .= $client->read();
                $commandCount = Iter\count(Byte\split($pipelined, "\r\n")) - 1;
            }

            // Reject MAIL FROM
            $client->writeAll("550 Sender rejected\r\n");
            $client->writeAll("503 Bad sequence\r\n");
            $client->writeAll("503 Bad sequence\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('badsender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Pipeline Reject')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testPipeliningRecipientRejected(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 PIPELINING\r\n");

            $pipelined = '';
            $commandCount = 0;
            while ($commandCount < 3) {
                $pipelined .= $client->read();
                $commandCount = Iter\count(Byte\split($pipelined, "\r\n")) - 1;
            }

            // Accept MAIL FROM, reject RCPT
            $client->writeAll("250 OK\r\n");
            $client->writeAll("550 User unknown\r\n");
            $client->writeAll("503 Bad sequence\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('unknown', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Pipeline Reject RCPT')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(TransmissionException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testRsetFailedThrowsProtocolException(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            // RSET fails
            self::readCommand($client);
            $client->writeAll("421 Service not available\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ProtocolException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    public function testSendWithPipeliningMultipleRecipients(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250-mail.example.com\r\n250 PIPELINING\r\n");

            // 4 commands: MAIL FROM + 2x RCPT TO + DATA
            $pipelined = '';
            $commandCount = 0;
            while ($commandCount < 4) {
                $pipelined .= $client->read();
                $commandCount = Iter\count(Byte\split($pipelined, "\r\n")) - 1;
            }

            static::assertStringContainsString('MAIL FROM:<sender@example.com>', $pipelined);
            static::assertStringContainsString('RCPT TO:<alice@example.com>', $pipelined);
            static::assertStringContainsString('RCPT TO:<bob@example.com>', $pipelined);
            static::assertStringContainsString('DATA', $pipelined);

            $client->writeAll("250 OK\r\n");
            $client->writeAll("250 OK\r\n");
            $client->writeAll("250 OK\r\n");
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $alice = new Mailbox('alice', 'example.com');
        $bob = new Mailbox('bob', 'example.com');
        $envelope = new Envelope($sender, [$alice, $bob]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($alice, $bob)
            ->withSubject('Pipeline Multi')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $transport->send($envelope, $message);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testCloseMultipleTimes(): void
    {
        $configuration = new TransportConfiguration(host: '127.0.0.1', port: 1, security: Security::None);
        $transport = new Transport($configuration);

        // Should not throw when called multiple times
        $transport->close();
        $transport->close();

        $this->addToAssertionCount(1);
    }

    public function testSendWithDSNNullValues(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com ESMTP\r\n");

            self::readCommand($client);
            $client->writeAll("250 mail.example.com\r\n");

            $mailFrom = self::readCommand($client);
            // Should not contain RET or ENVID
            static::assertStringNotContainsString('RET=', $mailFrom);
            static::assertStringNotContainsString('ENVID=', $mailFrom);
            $client->writeAll("250 OK\r\n");

            $rcptTo = self::readCommand($client);
            static::assertStringNotContainsString('NOTIFY=', $rcptTo);
            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("354 Go ahead\r\n");

            $data = '';
            while (!Byte\contains($data, "\r\n.\r\n")) {
                $data .= $client->read();
            }

            $client->writeAll("250 OK\r\n");

            self::readCommand($client);
            $client->writeAll("250 OK\r\n");

            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('DSN Null')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        // SendConfiguration with all defaults
        $config = new SendConfiguration();
        $transport->send($envelope, $message, $config);
        $transport->close();
        $server->close();

        $this->addToAssertionCount(1);
    }

    public function testBothEhloAndHeloFail(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $address = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $client->writeAll("220 mail.example.com SMTP\r\n");

            // EHLO fails
            self::readCommand($client);
            $client->writeAll("502 Not implemented\r\n");

            // HELO also fails
            self::readCommand($client);
            $client->writeAll("550 Not allowed\r\n");
            $client->close();
        });

        $configuration = new TransportConfiguration(
            host: $address->host,
            port: $address->port,
            security: Security::None,
        );
        $transport = new Transport($configuration);

        $sender = new Mailbox('sender', 'example.com');
        $recipient = new Mailbox('recipient', 'example.com');
        $envelope = new Envelope($sender, [$recipient]);

        $message = new Message()
            ->withFrom($sender)
            ->withTo($recipient)
            ->withSubject('Test')
            ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

        $this->expectException(ProtocolException::class);

        try {
            $transport->send($envelope, $message);
        } finally {
            $transport->close();
            $server->close();
        }
    }

    private static function readCommand(Network\StreamInterface $stream): string
    {
        $command = '';
        while (!Byte\contains($command, "\r\n")) {
            $command .= $stream->read();
        }

        return $command;
    }
}
