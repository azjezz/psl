<?php

declare(strict_types=1);

namespace Psl\Message\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Message\Exception\ParsingException;
use Psl\Message\MessageId;

final class MessageIdTest extends TestCase
{
    #[Test]
    public function constructWithValidId(): void
    {
        $id = new MessageId('unique-id@example.com');

        self::assertSame('unique-id@example.com', $id->id);
    }

    #[Test]
    public function constructWithEmptyIdThrows(): void
    {
        $this->expectException(ParsingException::class);

        new MessageId('');
    }

    #[Test]
    public function generate(): void
    {
        $id = MessageId::generate();

        self::assertStringContainsString('@php-standard-library.dev', $id->id);
        self::assertNotEmpty($id->id);
    }

    #[Test]
    public function generateWithCustomDomain(): void
    {
        $id = MessageId::generate('example.com');

        self::assertStringContainsString('@example.com', $id->id);
    }

    #[Test]
    public function generateProducesUniqueIds(): void
    {
        $a = MessageId::generate();
        $b = MessageId::generate();

        self::assertNotSame($a->id, $b->id);
    }

    #[Test]
    public function parseAngleBracketForm(): void
    {
        $id = MessageId::parse('<unique@example.com>');

        self::assertSame('unique@example.com', $id->id);
    }

    #[Test]
    public function parseBareForm(): void
    {
        $id = MessageId::parse('unique@example.com');

        self::assertSame('unique@example.com', $id->id);
    }

    #[Test]
    public function parseTrimsWhitespace(): void
    {
        $id = MessageId::parse('  <unique@example.com>  ');

        self::assertSame('unique@example.com', $id->id);
    }

    #[Test]
    public function parseEmptyThrows(): void
    {
        $this->expectException(ParsingException::class);

        MessageId::parse('');
    }

    #[Test]
    public function parseEmptyAngleBracketsThrows(): void
    {
        $this->expectException(ParsingException::class);

        MessageId::parse('<>');
    }

    #[Test]
    public function parseWhitespaceOnlyThrows(): void
    {
        $this->expectException(ParsingException::class);

        MessageId::parse('   ');
    }

    #[Test]
    public function toStringReturnsAngleBracketForm(): void
    {
        $id = new MessageId('unique@example.com');

        self::assertSame('<unique@example.com>', $id->toString());
        self::assertSame('<unique@example.com>', (string) $id);
    }

    #[Test]
    public function parseBareIdWithoutAtSign(): void
    {
        $id = MessageId::parse('local-only');

        self::assertSame('local-only', $id->id);
    }
}
