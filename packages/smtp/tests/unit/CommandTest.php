<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Command;
use Stringable;

use function base64_encode;
use function str_repeat;

final class CommandTest extends TestCase
{
    public function testConstructorWithVerbOnly(): void
    {
        $command = new Command('QUIT');

        static::assertSame('QUIT', $command->verb);
        static::assertSame('', $command->argument);
    }

    public function testConstructorWithVerbAndArgument(): void
    {
        $command = new Command('EHLO', 'mail.example.com');

        static::assertSame('EHLO', $command->verb);
        static::assertSame('mail.example.com', $command->argument);
    }

    public function testToStringWithVerbOnly(): void
    {
        $command = new Command('QUIT');

        static::assertSame('QUIT', $command->toString());
    }

    public function testToStringWithVerbAndArgument(): void
    {
        $command = new Command('EHLO', 'client.example.com');

        static::assertSame('EHLO client.example.com', $command->toString());
    }

    public function testStringableInterface(): void
    {
        $command = new Command('NOOP');

        static::assertInstanceOf(Stringable::class, $command);
        static::assertSame('NOOP', (string) $command);
    }

    public function testStringableWithArgument(): void
    {
        $command = new Command('MAIL', 'FROM:<user@example.com>');

        static::assertSame('MAIL FROM:<user@example.com>', (string) $command);
    }

    public function testEhloCommand(): void
    {
        $command = new Command('EHLO', 'localhost');

        static::assertSame('EHLO', $command->verb);
        static::assertSame('localhost', $command->argument);
        static::assertSame('EHLO localhost', $command->toString());
    }

    public function testHeloCommand(): void
    {
        $command = new Command('HELO', 'localhost');

        static::assertSame('HELO localhost', $command->toString());
    }

    public function testMailFromCommand(): void
    {
        $command = new Command('MAIL', 'FROM:<sender@example.com>');

        static::assertSame('MAIL FROM:<sender@example.com>', $command->toString());
    }

    public function testRcptToCommand(): void
    {
        $command = new Command('RCPT', 'TO:<recipient@example.com>');

        static::assertSame('RCPT TO:<recipient@example.com>', $command->toString());
    }

    public function testDataCommand(): void
    {
        $command = new Command('DATA');

        static::assertSame('DATA', $command->toString());
    }

    public function testRsetCommand(): void
    {
        $command = new Command('RSET');

        static::assertSame('RSET', $command->toString());
    }

    public function testQuitCommand(): void
    {
        $command = new Command('QUIT');

        static::assertSame('QUIT', $command->toString());
    }

    public function testNoopCommand(): void
    {
        $command = new Command('NOOP');

        static::assertSame('NOOP', $command->toString());
    }

    public function testVrfyCommand(): void
    {
        $command = new Command('VRFY', 'postmaster');

        static::assertSame('VRFY postmaster', $command->toString());
    }

    public function testExpnCommand(): void
    {
        $command = new Command('EXPN', 'mailing-list');

        static::assertSame('EXPN mailing-list', $command->toString());
    }

    public function testEmptyArgument(): void
    {
        $command = new Command('DATA', '');

        static::assertSame('', $command->argument);
        static::assertSame('DATA', $command->toString());
    }

    public function testArgumentWithSpecialCharacters(): void
    {
        $command = new Command('MAIL', 'FROM:<user+tag@example.com> BODY=8BITMIME');

        static::assertSame('MAIL FROM:<user+tag@example.com> BODY=8BITMIME', $command->toString());
    }

    public function testArgumentWithSpaces(): void
    {
        $command = new Command('MAIL', 'FROM:<user@example.com> BODY=8BITMIME SMTPUTF8');

        static::assertSame('MAIL FROM:<user@example.com> BODY=8BITMIME SMTPUTF8', $command->toString());
    }

    public function testLongArgument(): void
    {
        $longDomain = str_repeat('a', 200) . '.example.com';
        $command = new Command('EHLO', $longDomain);

        static::assertSame('EHLO ' . $longDomain, $command->toString());
    }

    public function testStartTlsCommand(): void
    {
        $command = new Command('STARTTLS');

        static::assertSame('STARTTLS', $command->toString());
        static::assertSame('', $command->argument);
    }

    public function testAuthPlainCommand(): void
    {
        $payload = base64_encode("\0user\0pass");
        $command = new Command('AUTH', 'PLAIN ' . $payload);

        static::assertSame('AUTH', $command->verb);
        static::assertSame('PLAIN ' . $payload, $command->argument);
        static::assertSame('AUTH PLAIN ' . $payload, $command->toString());
    }

    public function testAuthLoginCommand(): void
    {
        $command = new Command('AUTH', 'LOGIN');

        static::assertSame('AUTH LOGIN', $command->toString());
    }

    public function testMailFromWithDsnParameters(): void
    {
        $command = new Command('MAIL', 'FROM:<user@example.com> RET=FULL ENVID=abc123');

        static::assertSame('MAIL FROM:<user@example.com> RET=FULL ENVID=abc123', $command->toString());
    }

    public function testRcptToWithNotifyParameter(): void
    {
        $command = new Command('RCPT', 'TO:<user@example.com> NOTIFY=SUCCESS,FAILURE');

        static::assertSame('RCPT TO:<user@example.com> NOTIFY=SUCCESS,FAILURE', $command->toString());
    }

    public function testMailFromEmptySender(): void
    {
        $command = new Command('MAIL', 'FROM:<>');

        static::assertSame('MAIL FROM:<>', $command->toString());
    }

    public function testToStringAndMagicToStringAreIdentical(): void
    {
        $command = new Command('EHLO', 'test.example.com');

        static::assertSame($command->toString(), $command->__toString());
    }

    public function testReadonlyProperties(): void
    {
        $command = new Command('EHLO', 'host');

        static::assertSame('EHLO', $command->verb);
        static::assertSame('host', $command->argument);
    }

    public function testArgumentWithAngleBrackets(): void
    {
        $command = new Command('RCPT', 'TO:<"special user"@example.com>');

        static::assertSame('RCPT TO:<"special user"@example.com>', $command->toString());
    }

    public function testArgumentWithEqualsSign(): void
    {
        $command = new Command('MAIL', 'FROM:<user@example.com> SIZE=1024');

        static::assertSame('MAIL FROM:<user@example.com> SIZE=1024', $command->toString());
    }

    public function testArgumentWithUnicodeCharacters(): void
    {
        $command = new Command('EHLO', 'xn--e1afmapc.xn--p1ai');

        static::assertSame('EHLO xn--e1afmapc.xn--p1ai', $command->toString());
    }

    public function testDefaultArgumentIsEmptyString(): void
    {
        $command = new Command('RSET');

        static::assertSame('', $command->argument);
    }
}
