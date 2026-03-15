<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\SecureRandom;

final class Base64MimeTest extends TestCase
{
    #[DataProvider('provideRandomBytes')]
    public function testEncodeAndDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::Mime);

        static::assertSame($random, Base64\decode($encoded, Base64\Variant::Mime));
    }

    #[DataProvider('provideRandomBytes')]
    public function testEncodeWithoutPaddingThenDecode(string $random): void
    {
        $encoded = Base64\encode($random, Base64\Variant::Mime, false);

        static::assertSame($random, Base64\decode($encoded, Base64\Variant::Mime, false));
    }

    public function testEncodeShortStringNoWrapping(): void
    {
        $encoded = Base64\encode('Hello', Base64\Variant::Mime);

        static::assertSame('SGVsbG8=', $encoded);
        static::assertStringNotContainsString("\r\n", $encoded);
    }

    public function testEncodeLongStringWrapsAt76Chars(): void
    {
        $input = str_repeat('A', 100);
        $encoded = Base64\encode($input, Base64\Variant::Mime);

        $lines = explode("\r\n", $encoded);

        foreach ($lines as $line) {
            static::assertLessThanOrEqual(76, strlen($line));
        }

        static::assertSame($input, Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testDecodeStripsWhitespace(): void
    {
        $encoded = "SGVs\r\nbG8s\r\nIFdv\r\ncmxk\r\nIQ==";

        static::assertSame('Hello, World!', Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testDecodeStripsTabs(): void
    {
        $encoded = "SGVsbG8s\tIFdvcmxkIQ==";

        static::assertSame('Hello, World!', Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testDecodeStripsSpaces(): void
    {
        $encoded = 'SGVsbG8s IFdvcmxkIQ==';

        static::assertSame('Hello, World!', Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testEncodeEmptyString(): void
    {
        static::assertSame('', Base64\encode('', Base64\Variant::Mime));
    }

    public function testDecodeEmptyString(): void
    {
        static::assertSame('', Base64\decode('', Base64\Variant::Mime));
    }

    public function testDecodeInvalidCharactersThrows(): void
    {
        $this->expectException(Exception\RangeException::class);

        Base64\decode('!!!invalid!!!', Base64\Variant::Mime);
    }

    public function testDecodeIncorrectPaddingThrows(): void
    {
        $this->expectException(Exception\IncorrectPaddingException::class);

        Base64\decode('SGVsbG8', Base64\Variant::Mime);
    }

    public function testDecodeIncorrectPaddingAllowedWhenExplicitPaddingDisabled(): void
    {
        static::assertSame('Hello', Base64\decode('SGVsbG8', Base64\Variant::Mime, false));
    }

    public function testEncodeBinaryData(): void
    {
        $binary = "\x00\x01\x02\xff\xfe\xfd";
        $encoded = Base64\encode($binary, Base64\Variant::Mime);

        static::assertSame($binary, Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testEncodeExactly76CharsNoWrapping(): void
    {
        // 57 raw bytes = exactly 76 base64 chars (no padding needed)
        $input = str_repeat('A', 57);
        $encoded = Base64\encode($input, Base64\Variant::Mime);

        static::assertSame(76, strlen($encoded));
        static::assertStringNotContainsString("\r\n", $encoded);
    }

    public function testEncode58BytesWraps(): void
    {
        // 58 raw bytes = 80 base64 chars, should wrap
        $input = str_repeat('A', 58);
        $encoded = Base64\encode($input, Base64\Variant::Mime);

        static::assertStringContainsString("\r\n", $encoded);

        $lines = explode("\r\n", $encoded);
        static::assertSame(76, strlen($lines[0]));
        static::assertSame($input, Base64\decode($encoded, Base64\Variant::Mime));
    }

    public function testStandardAndMimeDecodeInterop(): void
    {
        $input = 'Hello, World!';
        $standardEncoded = Base64\encode($input, Base64\Variant::Standard);

        // Standard encoded should be decodable by Mime (no whitespace, same alphabet)
        static::assertSame($input, Base64\decode($standardEncoded, Base64\Variant::Mime));
    }

    public static function provideRandomBytes(): iterable
    {
        for ($i = 1; $i < 128; ++$i) {
            yield [SecureRandom\bytes($i)];
        }
    }
}
