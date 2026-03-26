<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\MIME\TransferEncoding;
use Psl\Str;

final class TransferEncodingTest extends TestCase
{
    public function testValues(): void
    {
        static::assertSame('base64', TransferEncoding::Base64->value);
        static::assertSame('quoted-printable', TransferEncoding::QuotedPrintable->value);
        static::assertSame('7bit', TransferEncoding::SevenBit->value);
        static::assertSame('8bit', TransferEncoding::EightBit->value);
        static::assertSame('binary', TransferEncoding::Binary->value);
    }

    public function testFromString(): void
    {
        static::assertSame(TransferEncoding::Base64, TransferEncoding::from('base64'));
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::from('quoted-printable'));
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::from('7bit'));
    }

    public function testDetectEmpty(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect(''));
    }

    public function testDetectPureAscii(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect('Hello, World!'));
    }

    public function testDetectAsciiWithNewlines(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect("Hello\r\nWorld"));
    }

    public function testDetectFewHighBytes(): void
    {
        $content = Str\repeat('Hello ', 50) . "\xC3\xA9";

        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectManyHighBytes(): void
    {
        $content = Str\repeat("\xC3\xA9\xC3\xBC\xC3\xB6", 100);

        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect($content));
    }

    public function testDetectBinaryControlChars(): void
    {
        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect("Hello\x00World"));
    }

    public function testDetectLongLinesAscii(): void
    {
        $content = Str\repeat('a', 1000);

        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testTryFromInvalid(): void
    {
        static::assertNull(TransferEncoding::tryFrom('nonexistent'));
    }

    public function testDetectLongLinesWithHighBytes(): void
    {
        $content = Str\repeat("\xC3\xA9", 600);

        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect($content));
    }

    public function testDetectOnlyTabs(): void
    {
        $content = "\t\t\t\t\t";

        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectOnlyCrLf(): void
    {
        $content = "\r\n\r\n\r\n";

        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectSingleByte(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect('A'));
    }

    public function testDetectBinaryNull(): void
    {
        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect("\x00"));
    }

    public function testDetectMultipleControlBytes(): void
    {
        $content = "\x01\x02\x03\x04\x05";

        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect($content));
    }

    public function testDetectAsciiAtLineLimit(): void
    {
        $content = Str\repeat('a', 998);

        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectAsciiOverLineLimit(): void
    {
        $content = Str\repeat('a', 999);

        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectHighBytesAtExactLineLimit(): void
    {
        $content = Str\repeat('a', 996) . "\xC3\xA9";

        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectHighBytesOverLineLimit(): void
    {
        $content = Str\repeat('a', 997) . "\xC3\xA9";

        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect($content));
    }

    public function testDetectEmptyReturnsSevenBit(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect(''));
    }

    public function testDetectEmptyReturnsSevenBitNotNull(): void
    {
        $result = TransferEncoding::detect('');
        static::assertSame(TransferEncoding::SevenBit, $result);
    }

    public function testDetectMultipleLinesAscii(): void
    {
        $content = "line1\nline2\nline3\nline4";
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectContentWithCarriageReturns(): void
    {
        $content = "line1\r\nline2\r\nline3";
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectByte127IsNotHighByte(): void
    {
        $content = "Hello\x7FWorld";
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectPureAsciiShortLineIsSevenBit(): void
    {
        $content = 'Hello, this is purely ASCII text with no high bytes.';
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectHighBytesAtExactThirtyPercent(): void
    {
        $content = 'aaaaaaa' . "\xC0\xC1\xC2";
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectAsciiExactly998CharsIsSevenBit(): void
    {
        $content = Str\repeat('a', 998);
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectAscii999CharsIsQuotedPrintable(): void
    {
        $content = Str\repeat('a', 999);
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectEmptyReturnsSevenBitValue(): void
    {
        $result = TransferEncoding::detect('');
        static::assertSame('7bit', $result->value);
        static::assertNotSame(TransferEncoding::Base64, $result);
        static::assertNotSame(TransferEncoding::QuotedPrintable, $result);
        static::assertNotSame(TransferEncoding::EightBit, $result);
        static::assertNotSame(TransferEncoding::Binary, $result);
    }

    public function testDetectMultipleLinesProcessesAllLines(): void
    {
        $content = Str\repeat('a', 100) . "\n" . Str\repeat('b', 1000);
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectCRIsSkippedInLineLength(): void
    {
        $content = "abc\r\n" . Str\repeat('d', 1000);
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectZeroHighBytesWithExactLineLimit(): void
    {
        $content = Str\repeat('a', 998);
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::SevenBit, $result);
    }

    public function testDetectZeroHighBytesAtExactLineLimitBoundary(): void
    {
        $sevenBit = TransferEncoding::detect(Str\repeat('a', 998));
        $qp = TransferEncoding::detect(Str\repeat('a', 999));
        static::assertSame(TransferEncoding::SevenBit, $sevenBit);
        static::assertSame(TransferEncoding::QuotedPrintable, $qp);
    }

    public function testDetectHighBytesZeroAndLineLengthExactly998IsSevenBit(): void
    {
        $content = Str\repeat('x', 998);
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectEmptyReturnsSevenBitNotOtherEncoding(): void
    {
        $result = TransferEncoding::detect('');
        static::assertSame(TransferEncoding::SevenBit, $result);
        static::assertNotSame(TransferEncoding::Binary, $result);
        static::assertNotSame(TransferEncoding::EightBit, $result);
    }

    public function testDetectEmptyStringDoesNotThrow(): void
    {
        $result = TransferEncoding::detect('');
        static::assertInstanceOf(TransferEncoding::class, $result);
        static::assertSame('7bit', $result->value);
    }

    public function testDetectEmptyStringReturnsExactSevenBitInstance(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect(''));
        static::assertTrue(TransferEncoding::detect('') === TransferEncoding::SevenBit);
    }

    public function testDetectSingleByteProcessedCorrectly(): void
    {
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect('X'));
        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect("\x00"));
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect("\t"));
    }

    public function testDetectSubstrExtractsSingleByte(): void
    {
        $content = Str\repeat('a', 10) . "\xC3\xA9";
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::QuotedPrintable, $result);
    }

    public function testDetectSingleHighByteIsBase64DueToRatio(): void
    {
        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect("\x80"));
        static::assertSame(TransferEncoding::Base64, TransferEncoding::detect("\xFF"));
    }

    public function testDetectZeroHighBytesWithLineLengthExactly998(): void
    {
        $content = Str\repeat('a', 998);
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectZeroHighBytesNonZeroHighByteBoundary(): void
    {
        $content = Str\repeat('a', 997) . "\x80";
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectAllAsciiNoHighBytesWithShortLines(): void
    {
        $content = Str\repeat('a', 50) . "\n" . Str\repeat('b', 50);
        static::assertSame(TransferEncoding::SevenBit, TransferEncoding::detect($content));
    }

    public function testDetectHighBytesOnlyLineWithin998(): void
    {
        $content = Str\repeat('a', 900) . "\x80\x81\x82";
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::QuotedPrintable, $result);
    }

    public function testDetectHighBytesAbove30PercentReturnsBase64(): void
    {
        $content = "aaa\x80\x81\x82\x83";
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::Base64, $result);
    }

    public function testDetectPureAsciiLineLengthExactly999IsQuotedPrintable(): void
    {
        $content = Str\repeat('a', 999);
        static::assertSame(TransferEncoding::QuotedPrintable, TransferEncoding::detect($content));
    }

    public function testDetectPureAsciiLineLengthExactly998IsSevenBitNotQP(): void
    {
        $content = Str\repeat('a', 998);
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::SevenBit, $result);
        static::assertNotSame(TransferEncoding::QuotedPrintable, $result);
    }

    public function testDetectHighBytesGreaterThanZeroCondition(): void
    {
        $content = Str\repeat('a', 990) . "\xC0";
        $result = TransferEncoding::detect($content);
        static::assertSame(TransferEncoding::QuotedPrintable, $result);
        static::assertNotSame(TransferEncoding::SevenBit, $result);
    }
}
