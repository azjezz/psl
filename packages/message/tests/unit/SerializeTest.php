<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\Message;
use Psl\MIME\Headers;
use Psl\MIME\Part;

use function explode;
use function str_repeat;
use function strlen;

final class SerializeTest extends TestCase
{
    #[Test]
    public function serializeSimpleTextMessage(): void
    {
        $message = new Message\Message()
            ->withFrom(Message\Address\AddressList::parse('alice@example.com'))
            ->withTo(Message\Address\AddressList::parse('bob@example.com'))
            ->withSubject('Hello')
            ->withBody(new Part\Text(new IO\MemoryHandle('Hello, Bob!')));

        $result = Message\serialize($message)->readAll();

        self::assertStringContainsString("From: alice@example.com\r\n", $result);
        self::assertStringContainsString("To: bob@example.com\r\n", $result);
        self::assertStringContainsString("Subject: Hello\r\n", $result);
        self::assertStringContainsString('Hello, Bob!', $result);
    }

    #[Test]
    public function serializeEmptyBody(): void
    {
        $message = new Message\Message(Headers::fromPairs([
            ['Subject', 'Empty'],
        ]));

        $result = Message\serialize($message)->readAll();

        self::assertStringContainsString("Subject: Empty\r\n", $result);
    }

    #[Test]
    public function roundTripParseSerialize(): void
    {
        $message = new Message\Message()
            ->withFrom(Message\Address\AddressList::parse('alice@example.com'))
            ->withTo(Message\Address\AddressList::parse('bob@example.com'))
            ->withSubject('Hello')
            ->withBody(new Part\Text(new IO\MemoryHandle('Body here')));

        $serialized = Message\serialize($message)->readAll();
        $reparsed = Message\parse($serialized);

        self::assertSame($message->subject, $reparsed->subject);
        self::assertSame($message->from?->mailboxes()[0]?->address, $reparsed->from?->mailboxes()[0]?->address);
    }

    #[Test]
    public function serializeFoldsLongHeaders(): void
    {
        $longSubject = str_repeat('word ', 20);
        $message = new Message\Message()
            ->withSubject($longSubject)
            ->withBody(new Part\Text(new IO\MemoryHandle('')));

        $result = Message\serialize($message)->readAll();

        $lines = explode("\r\n", $result);
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            self::assertLessThanOrEqual(998, strlen($line));
        }
    }
}
