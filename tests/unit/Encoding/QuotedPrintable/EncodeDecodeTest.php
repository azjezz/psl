<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding\QuotedPrintable;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Encoding\QuotedPrintable;

final class EncodeDecodeTest extends TestCase
{
    public function testEncodeEmptyString(): void
    {
        static::assertSame('', QuotedPrintable\encode(''));
    }

    public function testDecodeEmptyString(): void
    {
        static::assertSame('', QuotedPrintable\decode(''));
    }

    public function testEncodePlainAscii(): void
    {
        static::assertSame('Hello, World!', QuotedPrintable\encode('Hello, World!'));
    }

    public function testEncodeEqualsSign(): void
    {
        static::assertSame('3+3=3D6', QuotedPrintable\encode('3+3=6'));
    }

    public function testEncodeTabInMiddle(): void
    {
        $encoded = QuotedPrintable\encode("a\tb");

        static::assertSame("a\tb", $encoded);
    }

    public function testEncodeTrailingTab(): void
    {
        $encoded = QuotedPrintable\encode("a\t");

        static::assertSame('a=09', $encoded);
    }

    public function testEncodeTrailingSpace(): void
    {
        $encoded = QuotedPrintable\encode('a ');

        static::assertSame('a=20', $encoded);
    }

    public function testEncodeSpaceInMiddle(): void
    {
        $encoded = QuotedPrintable\encode('a b');

        static::assertSame('a b', $encoded);
    }

    public function testEncodeHighBytes(): void
    {
        $encoded = QuotedPrintable\encode("\xff\xfe");

        static::assertSame('=FF=FE', $encoded);
    }

    public function testEncodeNullByte(): void
    {
        $encoded = QuotedPrintable\encode("\x00");

        static::assertSame('=00', $encoded);
    }

    public function testEncodeControlCharacters(): void
    {
        $encoded = QuotedPrintable\encode("\x01\x02\x03");

        static::assertSame('=01=02=03', $encoded);
    }

    public function testEncodeLineBreaks(): void
    {
        $encoded = QuotedPrintable\encode("line1\r\nline2");

        static::assertSame("line1\r\nline2", $encoded);
    }

    public function testEncodeBareLF(): void
    {
        $encoded = QuotedPrintable\encode("line1\nline2");

        static::assertSame("line1\r\nline2", $encoded);
    }

    public function testEncodeBareCR(): void
    {
        $encoded = QuotedPrintable\encode("line1\rline2");

        static::assertSame("line1\r\nline2", $encoded);
    }

    public function testEncodeLongLineSoftBreak(): void
    {
        $input = str_repeat('A', 100);
        $encoded = QuotedPrintable\encode($input);

        $lines = explode("\r\n", $encoded);

        foreach ($lines as $line) {
            static::assertLessThanOrEqual(76, strlen($line));
        }

        static::assertSame($input, QuotedPrintable\decode($encoded));
    }

    public function testEncodeLongLineWithSpecialCharsSoftBreak(): void
    {
        $input = str_repeat('=', 30);
        $encoded = QuotedPrintable\encode($input);

        $lines = explode("\r\n", $encoded);

        foreach ($lines as $line) {
            static::assertLessThanOrEqual(76, strlen($line));
        }

        static::assertSame($input, QuotedPrintable\decode($encoded));
    }

    /**
     * @param non-empty-string $input
     */
    #[DataProvider('provideRoundTripData')]
    public function testRoundTrip(string $input): void
    {
        $encoded = QuotedPrintable\encode($input);
        $decoded = QuotedPrintable\decode($encoded);

        static::assertSame($input, $decoded);
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function provideRoundTripData(): iterable
    {
        yield 'plain ascii' => ['Hello, World!'];
        yield 'equals sign' => ['a=b=c'];
        yield 'high bytes' => ["\x80\x90\xa0\xb0\xc0\xd0\xe0\xf0"];
        yield 'mixed content' => ["Subject: =?UTF-8?Q?t=C3=A9st?=\r\nFrom: user@example.com"];
        yield 'long line' => [str_repeat('X', 200)];
        yield 'trailing whitespace' => ["hello \r\nworld\t"];
        yield 'all printable' => [
            '!"#$%&\'()*+,-./0123456789:;<>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~',
        ];
        yield 'binary data' => ["\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0b\x0c\x0e\x0f"];
    }

    public function testDecodePreEncoded(): void
    {
        static::assertSame('Hello, World!', QuotedPrintable\decode('Hello, World!'));
        static::assertSame('3+3=6', QuotedPrintable\decode('3+3=3D6'));
        static::assertSame("\xff", QuotedPrintable\decode('=FF'));
    }

    public function testDecodeSoftLineBreak(): void
    {
        static::assertSame('hello world', QuotedPrintable\decode("hello =\r\nworld"));
    }

    public function testEncodeMultipleLines(): void
    {
        $input = "line1\r\nline2\r\nline3";
        $encoded = QuotedPrintable\encode($input);

        static::assertSame("line1\r\nline2\r\nline3", $encoded);
    }

    public function testEncodeLineExactlyAtMaxLength(): void
    {
        $input = str_repeat('A', 75);
        $encoded = QuotedPrintable\encode($input);

        static::assertSame($input, $encoded);
        static::assertSame(75, strlen($encoded));
    }

    public function testEncodeLineOneOverMaxLength(): void
    {
        $input = str_repeat('A', 76);
        $encoded = QuotedPrintable\encode($input);

        static::assertStringContainsString("=\r\n", $encoded);
        static::assertSame($input, QuotedPrintable\decode($encoded));
    }

    public function testEncodeCustomMaxLineLength(): void
    {
        $input = str_repeat('A', 50);
        $encoded = QuotedPrintable\encode($input, max_line_length: 30);

        $lines = explode("\r\n", $encoded);
        foreach ($lines as $line) {
            static::assertLessThanOrEqual(30, strlen($line));
        }

        static::assertSame($input, QuotedPrintable\decode($encoded));
    }

    public function testEncodeCustomLineEnding(): void
    {
        $input = "line1\r\nline2";
        $encoded = QuotedPrintable\encode($input, line_ending: "\n");

        static::assertSame("line1\nline2", $encoded);
    }

    public function testEncodeCustomLineEndingInSoftBreak(): void
    {
        $input = str_repeat('A', 100);
        $encoded = QuotedPrintable\encode($input, line_ending: "\n");

        static::assertStringContainsString("=\n", $encoded);
        static::assertStringNotContainsString("=\r\n", $encoded);
    }

    public function testEncodeLineCustomMaxLength(): void
    {
        $input = str_repeat('B', 20);
        $encoded = QuotedPrintable\encode_line($input, max_line_length: 10);

        $lines = explode("\r\n", $encoded);
        foreach ($lines as $line) {
            static::assertLessThanOrEqual(10, strlen($line));
        }
    }

    public function testEncodeLineCustomLineEnding(): void
    {
        $input = str_repeat('C', 100);
        $encoded = QuotedPrintable\encode_line($input, line_ending: "\n");

        static::assertStringContainsString("=\n", $encoded);
        static::assertStringNotContainsString("=\r\n", $encoded);
    }
}
