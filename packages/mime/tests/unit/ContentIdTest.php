<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\MIME\ContentId;
use Psl\MIME\Exception\ContentIdParsingException;
use Psl\Str\Byte;
use Stringable;

final class ContentIdTest extends TestCase
{
    public function testConstruction(): void
    {
        $id = new ContentId('part1@example.com');

        static::assertSame('part1@example.com', $id->id);
    }

    public function testEmptyThrows(): void
    {
        $this->expectException(ContentIdParsingException::class);

        new ContentId('');
    }

    public function testGenerate(): void
    {
        $id = ContentId::generate();

        static::assertStringContainsString('@Psl.local', $id->id);
        static::assertSame(32 + 1 + 9, Byte\length($id->id));
    }

    public function testGenerateCustomDomain(): void
    {
        $id = ContentId::generate('example.com');

        static::assertStringContainsString('@example.com', $id->id);
    }

    public function testGenerateUniqueness(): void
    {
        $a = ContentId::generate();
        $b = ContentId::generate();

        static::assertNotSame($a->id, $b->id);
    }

    public function testParseAngleBrackets(): void
    {
        $id = ContentId::parse('<part1@example.com>');

        static::assertSame('part1@example.com', $id->id);
    }

    public function testParseBare(): void
    {
        $id = ContentId::parse('part1@example.com');

        static::assertSame('part1@example.com', $id->id);
    }

    public function testParseCidUri(): void
    {
        $id = ContentId::parse('cid:part1@example.com');

        static::assertSame('part1@example.com', $id->id);
    }

    public function testParseEmptyThrows(): void
    {
        $this->expectException(ContentIdParsingException::class);

        ContentId::parse('');
    }

    public function testParseEmptyAngleBracketsThrows(): void
    {
        $this->expectException(ContentIdParsingException::class);

        ContentId::parse('<>');
    }

    public function testParseTrimsWhitespace(): void
    {
        $id = ContentId::parse('  <part1@example.com>  ');

        static::assertSame('part1@example.com', $id->id);
    }

    public function testToString(): void
    {
        $id = new ContentId('part1@example.com');

        static::assertSame('<part1@example.com>', $id->toString());
    }

    public function testToCidUri(): void
    {
        $id = new ContentId('part1@example.com');

        static::assertSame('cid:part1@example.com', $id->toCidUri());
    }

    public function testRoundTripAngleBrackets(): void
    {
        $id = ContentId::generate();
        $serialized = $id->toString();
        $reparsed = ContentId::parse($serialized);

        static::assertSame($id->id, $reparsed->id);
    }

    public function testRoundTripCidUri(): void
    {
        $id = ContentId::generate();
        $uri = $id->toCidUri();
        $reparsed = ContentId::parse($uri);

        static::assertSame($id->id, $reparsed->id);
    }

    public function testStringable(): void
    {
        $id = new ContentId('part1@example.com');

        static::assertInstanceOf(Stringable::class, $id);
        static::assertSame('<part1@example.com>', (string) $id);
    }

    public function testParseWhitespaceOnlyThrows(): void
    {
        $this->expectException(ContentIdParsingException::class);

        ContentId::parse('   ');
    }

    public function testParseCidEmptyIdThrows(): void
    {
        $this->expectException(ContentIdParsingException::class);

        ContentId::parse('cid:');
    }

    public function testParseBareNoAt(): void
    {
        $id = ContentId::parse('simple-id');

        static::assertSame('simple-id', $id->id);
    }

    public function testGenerateFormat(): void
    {
        $id = ContentId::generate();

        static::assertMatchesRegularExpression('/^[0-9a-f]{32}@Psl\.local$/', $id->id);
    }

    public function testToStringAngleBrackets(): void
    {
        $id = new ContentId('test@example.com');

        static::assertSame('<test@example.com>', $id->toString());
    }

    public function testParseOpenBracketOnlyIsBareForm(): void
    {
        $id = ContentId::parse('<part1@example.com');

        static::assertSame('<part1@example.com', $id->id);
    }

    public function testParseCloseBracketOnlyIsBareForm(): void
    {
        $id = ContentId::parse('part1@example.com>');

        static::assertSame('part1@example.com>', $id->id);
    }
}
