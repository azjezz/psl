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
}
