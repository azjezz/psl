<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit\Address;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Address\Mailbox;
use Psl\Message\Exception\InvalidMailboxException;
use Psl\Message\Exception\ParsingException;

final class MailboxTest extends TestCase
{
    #[Test]
    public function constructWithoutDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com');

        self::assertSame('user', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertNull($mailbox->displayName);
        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function constructWithDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', 'John Doe');

        self::assertSame('John Doe', $mailbox->displayName);
        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function constructEmptyLocalPartThrows(): void
    {
        $this->expectException(InvalidMailboxException::class);

        new Mailbox('', 'example.com');
    }

    #[Test]
    public function constructEmptyDomainThrows(): void
    {
        $this->expectException(InvalidMailboxException::class);

        new Mailbox('user', '');
    }

    #[Test]
    public function parseBareAddress(): void
    {
        $mailbox = Mailbox::parse('user@example.com');

        self::assertSame('user', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertNull($mailbox->displayName);
    }

    #[Test]
    public function parseAngleBracketAddress(): void
    {
        $mailbox = Mailbox::parse('<user@example.com>');

        self::assertSame('user', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertNull($mailbox->displayName);
    }

    #[Test]
    public function parseWithQuotedDisplayName(): void
    {
        $mailbox = Mailbox::parse('"John Doe" <user@example.com>');

        self::assertSame('user', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertSame('John Doe', $mailbox->displayName);
    }

    #[Test]
    public function parseWithUnquotedDisplayName(): void
    {
        $mailbox = Mailbox::parse('John Doe <user@example.com>');

        self::assertSame('John Doe', $mailbox->displayName);
        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function parseWithEncodedWordDisplayName(): void
    {
        $mailbox = Mailbox::parse('=?utf-8?B?SsO2aG4=?= <user@example.com>');

        self::assertSame("J\u{00F6}hn", $mailbox->displayName);
        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function parseTrimsWhitespace(): void
    {
        $mailbox = Mailbox::parse('  user@example.com  ');

        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function parseEmptyThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('');
    }

    #[Test]
    public function parseNoAtSignThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('invalid');
    }

    #[Test]
    public function parseMissingClosingAngleBracketThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('<user@example.com');
    }

    #[Test]
    public function parseAtSignAtStartThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('@example.com');
    }

    #[Test]
    public function parseAtSignAtEndThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('user@');
    }

    #[Test]
    public function toStringWithoutDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com');

        self::assertSame('user@example.com', $mailbox->toString());
        self::assertSame('user@example.com', (string) $mailbox);
    }

    #[Test]
    public function toStringWithDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', 'John Doe');

        self::assertSame('John Doe <user@example.com>', $mailbox->toString());
    }

    #[Test]
    public function toStringWithSpecialCharDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', 'Doe, John');

        self::assertSame('"Doe, John" <user@example.com>', $mailbox->toString());
    }

    #[Test]
    public function toStringWithNonAsciiDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', "J\u{00F6}hn");

        $result = $mailbox->toString();
        self::assertStringContainsString('=?', $result);
        self::assertStringContainsString('<user@example.com>', $result);
    }

    #[Test]
    public function toStringWithEmptyDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', '');

        self::assertSame('user@example.com', $mailbox->toString());
    }

    #[Test]
    public function toStringWithQuotesInDisplayName(): void
    {
        $mailbox = new Mailbox('user', 'example.com', 'O\'Brien, "Jim"');

        $result = $mailbox->toString();
        self::assertStringContainsString('<user@example.com>', $result);
    }

    #[Test]
    public function parseRoundTrip(): void
    {
        $original = new Mailbox('user', 'example.com', 'John Doe');
        $parsed = Mailbox::parse($original->toString());

        self::assertSame($original->address, $parsed->address);
        self::assertSame($original->displayName, $parsed->displayName);
    }

    #[Test]
    public function parseWithSubaddress(): void
    {
        $mailbox = Mailbox::parse('user+tag@example.com');

        self::assertSame('user+tag', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
    }

    #[Test]
    public function constructWithComment(): void
    {
        $mailbox = new Mailbox('user', 'example.com', null, 'a]comment');

        self::assertSame('a]comment', $mailbox->comment);
        self::assertSame('user@example.com', $mailbox->address);
    }

    #[Test]
    public function parseCommentAfterAddress(): void
    {
        $mailbox = Mailbox::parse('john@example.com (John Smith)');

        self::assertSame('john', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertSame('John Smith', $mailbox->comment);
        self::assertNull($mailbox->displayName);
    }

    #[Test]
    public function parseCommentInAddrSpec(): void
    {
        $mailbox = Mailbox::parse('john@(comment)example.com');

        self::assertSame('john', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
        self::assertSame('comment', $mailbox->comment);
    }

    #[Test]
    public function parseCommentBeforeAngleBracket(): void
    {
        $mailbox = Mailbox::parse('"John" (comment) <john@example.com>');

        self::assertSame('John', $mailbox->displayName);
        self::assertSame('john@example.com', $mailbox->address);
        self::assertSame('comment', $mailbox->comment);
    }

    #[Test]
    public function parseNestedComment(): void
    {
        $mailbox = Mailbox::parse('john@example.com (outer (inner) text)');

        self::assertSame('john@example.com', $mailbox->address);
        self::assertSame('outer (inner) text', $mailbox->comment);
    }

    #[Test]
    public function parseEscapedParenInComment(): void
    {
        $mailbox = Mailbox::parse('john@example.com (has \) paren)');

        self::assertSame('john@example.com', $mailbox->address);
        self::assertSame('has ) paren', $mailbox->comment);
    }

    #[Test]
    public function parseNoCommentReturnsNull(): void
    {
        $mailbox = Mailbox::parse('john@example.com');

        self::assertNull($mailbox->comment);
    }

    #[Test]
    public function toStringWithComment(): void
    {
        $mailbox = new Mailbox('john', 'example.com', null, 'a comment');

        self::assertSame('john@example.com (a comment)', $mailbox->toString());
    }

    #[Test]
    public function toStringWithDisplayNameAndComment(): void
    {
        $mailbox = new Mailbox('john', 'example.com', 'John Doe', 'a comment');

        self::assertSame('John Doe <john@example.com> (a comment)', $mailbox->toString());
    }

    #[Test]
    public function roundTripWithComment(): void
    {
        $original = new Mailbox('john', 'example.com', 'John Doe', 'a comment');
        $parsed = Mailbox::parse($original->toString());

        self::assertSame($original->address, $parsed->address);
        self::assertSame($original->displayName, $parsed->displayName);
        self::assertSame($original->comment, $parsed->comment);
    }

    #[Test]
    public function parseAngleBracketWithoutAtThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('<no-at-sign>');
    }

    #[Test]
    public function parseEmptyAngleBracketThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('<>');
    }

    #[Test]
    public function parseOnlyCommentThrows(): void
    {
        $this->expectException(ParsingException::class);

        Mailbox::parse('(just a comment)');
    }

    #[Test]
    public function parseEscapedCharOutsideComment(): void
    {
        $mailbox = Mailbox::parse('john@example.com');

        self::assertSame('john@example.com', $mailbox->address);
    }

    #[Test]
    public function parseMultipleAtSignsUsesLast(): void
    {
        $mailbox = Mailbox::parse('"user@host"@example.com');

        self::assertSame('"user@host"', $mailbox->localPart);
        self::assertSame('example.com', $mailbox->domain);
    }
}
