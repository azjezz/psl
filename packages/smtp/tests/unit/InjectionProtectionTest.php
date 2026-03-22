<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network;
use Psl\SMTP\Client\Connection;
use Psl\SMTP\Command;
use Psl\SMTP\Exception\PossibleAttackException;
use Psl\Str\Byte;
use Psl\TCP;

final class InjectionProtectionTest extends TestCase
{
    /**
     * CRLF in Command verb, sent via writeCommand.
     */
    public function testCRLFInCommandVerb(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command("EHLO\r\nMAIL FROM:<attacker@evil.com>", 'host'));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF in Command argument, sent via writeCommand.
     */
    public function testCRLFInCommandArgument(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command('MAIL', "FROM:<user@example.com>\r\nRCPT TO:<attacker@evil.com>"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF in raw string command via writeCommand.
     */
    public function testCRLFInRawStringWriteCommand(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("MAIL FROM:<user@example.com>\r\nRCPT TO:<attacker@evil.com>");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF in raw string command via sendCommand.
     */
    public function testCRLFInRawStringSendCommand(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->sendCommand("NOOP\r\nMAIL FROM:<attacker@evil.com>");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Lone CR in command argument.
     */
    public function testLoneCRInArgument(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command('EHLO', "host\rinjected"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Lone LF in command argument.
     */
    public function testLoneLFInArgument(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command('EHLO', "host\ninjected"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Lone CR in raw string.
     */
    public function testLoneCRInRawString(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("NOOP\rinjected");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Lone LF in raw string.
     */
    public function testLoneLFInRawString(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("NOOP\ninjected");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Null byte in Command verb.
     */
    public function testNullByteInCommandVerb(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command("EHLO\0", 'host'));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Null byte in Command argument.
     */
    public function testNullByteInCommandArgument(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command('MAIL', "FROM:<user\0@evil.com>"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Null byte in raw string command.
     */
    public function testNullByteInRawString(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("NOOP\0injected");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Null byte in raw string via sendCommand.
     */
    public function testNullByteInRawStringSendCommand(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->sendCommand("NOOP\0injected");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF injection in MAIL FROM address.
     */
    public function testCRLFInjectionInMailFromAddress(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->sendCommand(new Command('MAIL', "FROM:<user@example.com>\r\nRCPT TO:<attacker@evil.com>"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF injection in RCPT TO address.
     */
    public function testCRLFInjectionInRcptToAddress(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->sendCommand(new Command('RCPT', "TO:<user@example.com>\r\nDATA"));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * CRLF injection attempting to start DATA prematurely.
     */
    public function testCRLFInjectionAttemptingDataStart(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand(new Command('EHLO', "host\r\nDATA\r\ninjected body\r\n."));
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Multiple CRLF sequences in a single string.
     */
    public function testMultipleCRLFSequences(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("NOOP\r\nNOOP\r\nNOOP");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Null byte followed by CRLF.
     */
    public function testNullByteThenCRLF(): void
    {
        [$connection, $serverStream] = $this->createPair();

        $this->expectException(PossibleAttackException::class);

        try {
            $connection->writeCommand("NOOP\0\r\nMAIL FROM:<evil@evil.com>");
        } finally {
            $connection->close();
            $serverStream->close();
        }
    }

    /**
     * Verify that safe commands still work after injection checks.
     */
    public function testSafeCommandStillWorks(): void
    {
        [$connection, $serverStream] = $this->createPair();

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertSame("NOOP\r\n", $command);
            $serverStream->writeAll("250 OK\r\n");
        });

        $reply = $connection->sendCommand(new Command('NOOP'));

        static::assertSame(250, $reply->code);

        $connection->close();
        $serverStream->close();
    }

    /**
     * Verify safe raw string command still works.
     */
    public function testSafeRawStringStillWorks(): void
    {
        [$connection, $serverStream] = $this->createPair();

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertSame("NOOP\r\n", $command);
            $serverStream->writeAll("250 OK\r\n");
        });

        $reply = $connection->sendCommand('NOOP');

        static::assertSame(250, $reply->code);

        $connection->close();
        $serverStream->close();
    }

    /**
     * Verify Command with special but safe characters works.
     */
    public function testCommandWithSafeSpecialCharacters(): void
    {
        [$connection, $serverStream] = $this->createPair();

        Async\run(static function () use ($serverStream): void {
            $command = '';
            while (!Byte\contains($command, "\r\n")) {
                $command .= $serverStream->read();
            }

            static::assertStringContainsString('MAIL FROM:<user@example.com>', $command);
            $serverStream->writeAll("250 OK\r\n");
        });

        $reply = $connection->sendCommand(new Command('MAIL', 'FROM:<user@example.com> BODY=8BITMIME'));

        static::assertSame(250, $reply->code);

        $connection->close();
        $serverStream->close();
    }

    /**
     * @return array{Connection, Network\StreamInterface}
     *
     * @mago-expect analysis:possibly-null-argument
     * @mago-expect analysis:invalid-return-statement
     */
    private function createPair(): array
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

        return [new Connection($clientStream), $serverStream];
    }
}
