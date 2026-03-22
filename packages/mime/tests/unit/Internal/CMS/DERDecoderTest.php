<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal\CMS;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\CMSException;
use Psl\MIME\Internal\CMS\DERDecoder;
use Psl\MIME\Internal\CMS\DEREncoder;
use Psl\Str;

final class DERDecoderTest extends TestCase
{
    public function testParseSimpleElement(): void
    {
        $data = "\x02\x01\x42";
        [$tag, $content, $remainder] = DERDecoder::parse($data);

        static::assertSame(0x02, $tag);
        static::assertSame("\x42", $content);
        static::assertSame('', $remainder);
    }

    public function testParseWithRemainder(): void
    {
        $data = "\x02\x01\x42\x02\x01\x43";
        [$tag, $content, $remainder] = DERDecoder::parse($data);

        static::assertSame(0x02, $tag);
        static::assertSame("\x42", $content);
        static::assertSame("\x02\x01\x43", $remainder);
    }

    public function testParseMediumLength(): void
    {
        $content = Str\repeat('A', 200);
        $data = "\x04\x81\xc8" . $content;
        [$tag, $parsed, $remainder] = DERDecoder::parse($data);

        static::assertSame(0x04, $tag);
        static::assertSame($content, $parsed);
        static::assertSame('', $remainder);
    }

    public function testParseLongLength(): void
    {
        $content = Str\repeat('A', 300);
        $data = "\x04\x82\x01\x2c" . $content;
        [$tag, $parsed, $remainder] = DERDecoder::parse($data);

        static::assertSame(0x04, $tag);
        static::assertSame($content, $parsed);
        static::assertSame('', $remainder);
    }

    public function testParseAllElements(): void
    {
        $data = "\x02\x01\x42\x02\x01\x43\x02\x01\x44";
        $elements = DERDecoder::parseAll($data);

        static::assertCount(3, $elements);
        static::assertSame(0x02, $elements[0][0]);
        static::assertSame("\x42", $elements[0][1]);
        static::assertSame("\x43", $elements[1][1]);
        static::assertSame("\x44", $elements[2][1]);
    }

    public function testParseSequence(): void
    {
        $inner = "\x02\x01\x42";
        $data = DEREncoder::sequence($inner);
        $content = DERDecoder::parseSequence($data);

        static::assertSame($inner, $content);
    }

    public function testParseSequenceThrowsOnNonSequence(): void
    {
        $this->expectException(CMSException::class);

        DERDecoder::parseSequence("\x02\x01\x42");
    }

    public function testParseTooShortThrows(): void
    {
        $this->expectException(CMSException::class);

        DERDecoder::parse("\x02");
    }

    public function testParseContentExceedsBoundsThrows(): void
    {
        $this->expectException(CMSException::class);

        DERDecoder::parse("\x02\x05\x42");
    }

    public function testParseEmptyData(): void
    {
        $elements = DERDecoder::parseAll('');

        static::assertSame([], $elements);
    }

    public function testRoundTripWithEncoder(): void
    {
        $original = DEREncoder::sequence(DEREncoder::integer("\x42") . DEREncoder::octetString('hello'));

        $inner = DERDecoder::parseSequence($original);
        $elements = DERDecoder::parseAll($inner);

        static::assertCount(2, $elements);
        static::assertSame(0x02, $elements[0][0]);
        static::assertSame(0x04, $elements[1][0]);
        static::assertSame('hello', $elements[1][1]);
    }
}
