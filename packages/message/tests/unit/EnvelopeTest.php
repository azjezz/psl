<?php

declare(strict_types=1);

namespace Tests\Unit\Message;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Address\Mailbox;
use Psl\Message\Envelope;
use Psl\Message\Exception\InvalidArgumentException;
use Psl\Message\Exception\RuntimeException;
use Psl\Message\Message;
use Psl\MIME\Headers;

final class EnvelopeTest extends TestCase
{
    #[Test]
    public function constructWithSenderAndRecipients(): void
    {
        $sender = Mailbox::parse('alice@example.com');
        $recipient = Mailbox::parse('bob@example.com');

        $envelope = new Envelope($sender, [$recipient]);

        self::assertSame('alice@example.com', $envelope->sender?->address);
        self::assertCount(1, $envelope->recipients);
        self::assertSame('bob@example.com', $envelope->recipients[0]->address);
    }

    #[Test]
    public function constructWithNullSender(): void
    {
        $recipient = Mailbox::parse('bob@example.com');

        $envelope = new Envelope(null, [$recipient]);

        self::assertNull($envelope->sender);
    }

    #[Test]
    public function constructWithEmptyRecipientsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @mago-expect analysis:possibly-invalid-argument - intentionally testing empty array */
        new Envelope(Mailbox::parse('alice@example.com'), []);
    }

    #[Test]
    public function fromMessageBasic(): void
    {
        $message = new Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
            ['To',   'bob@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertSame('alice@example.com', $envelope->sender?->address);
        self::assertCount(1, $envelope->recipients);
        self::assertSame('bob@example.com', $envelope->recipients[0]->address);
    }

    #[Test]
    public function fromMessageWithCcAndBcc(): void
    {
        $message = new Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
            ['To',   'bob@example.com'],
            ['Cc',   'cc@example.com'],
            ['Bcc',  'bcc@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertCount(3, $envelope->recipients);
        self::assertSame('bob@example.com', $envelope->recipients[0]->address);
        self::assertSame('cc@example.com', $envelope->recipients[1]->address);
        self::assertSame('bcc@example.com', $envelope->recipients[2]->address);
    }

    #[Test]
    public function fromMessageSenderPreferredOverFrom(): void
    {
        $message = new Message(Headers::fromPairs([
            ['From',   'alice@example.com'],
            ['Sender', 'admin@example.com'],
            ['To',     'bob@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertSame('admin@example.com', $envelope->sender?->address);
    }

    #[Test]
    public function fromMessageFallsBackToFrom(): void
    {
        $message = new Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
            ['To',   'bob@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertSame('alice@example.com', $envelope->sender?->address);
    }

    #[Test]
    public function fromMessageNullSenderWhenNoFromOrSender(): void
    {
        $message = new Message(Headers::fromPairs([
            ['To', 'bob@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertNull($envelope->sender);
    }

    #[Test]
    public function fromMessageNoRecipientsThrows(): void
    {
        $this->expectException(RuntimeException::class);

        $message = new Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
        ]));

        Envelope::fromMessage($message);
    }

    #[Test]
    public function fromMessageFlattensGroups(): void
    {
        $message = new Message(Headers::fromPairs([
            ['To', 'Team: alice@example.com, bob@example.com;'],
        ]));

        $envelope = Envelope::fromMessage($message);

        self::assertCount(2, $envelope->recipients);
        self::assertSame('alice@example.com', $envelope->recipients[0]->address);
        self::assertSame('bob@example.com', $envelope->recipients[1]->address);
    }

    #[Test]
    public function fromMessageWithNullToAndBccCollectsFromCcAndBcc(): void
    {
        $message = new Message(Headers::fromPairs([
            ['From', 'alice@example.com'],
            ['Cc',   'cc@example.com'],
            ['Bcc',  'bcc@example.com'],
        ]));

        $envelope = Envelope::fromMessage($message);

        static::assertCount(2, $envelope->recipients);
        static::assertSame('cc@example.com', $envelope->recipients[0]->address);
        static::assertSame('bcc@example.com', $envelope->recipients[1]->address);
    }
}
