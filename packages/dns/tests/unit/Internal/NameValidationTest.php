<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Internal\Encoder;

use function implode;
use function str_repeat;

final class NameValidationTest extends TestCase
{
    public function testValidSimpleName(): void
    {
        $encoded = Encoder::encodeName('example.com');

        static::assertNotEmpty($encoded);
        static::assertSame("\x00", $encoded[-1]);
    }

    public function testValidSubdomain(): void
    {
        $encoded = Encoder::encodeName('a.b.c.d.example.com');

        static::assertNotEmpty($encoded);
    }

    public function testValidSingleLabel(): void
    {
        $encoded = Encoder::encodeName('localhost');

        static::assertNotEmpty($encoded);
    }

    public function testEmptyStringProducesRootName(): void
    {
        $encoded = Encoder::encodeName('');

        static::assertSame("\x00", $encoded);
    }

    public function testRootDotProducesRootName(): void
    {
        $encoded = Encoder::encodeName('.');

        static::assertSame("\x00", $encoded);
    }

    public function testTrailingDotIsValid(): void
    {
        $encoded = Encoder::encodeName('example.com.');

        static::assertNotEmpty($encoded);
    }

    public function testLabelExactly63CharsIsValid(): void
    {
        $label = str_repeat('a', 63);

        $encoded = Encoder::encodeName($label . '.com');

        static::assertNotEmpty($encoded);
    }

    public function testLabelOver63CharsThrows(): void
    {
        $label = str_repeat('a', 64);

        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName($label . '.com');
    }

    public function testNullByteInLabelThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName("evil\x00.example.com");
    }

    public function testNullByteInMiddleOfLabelThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName("abc\x00def.example.com");
    }

    public function testNameTotalLengthOver255Throws(): void
    {
        $labels = [];
        for ($i = 0; $i < 50; $i++) {
            $labels[] = str_repeat('a', 5);
        }

        $name = implode('.', $labels);

        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName($name);
    }

    public function testUnicodeLabelIsConvertedToPunycode(): void
    {
        $encoded = Encoder::encodeName('münchen.de');

        static::assertNotEmpty($encoded);
        static::assertStringContainsString('xn--', $encoded);
    }

    public function testMultipleUnicodeLabels(): void
    {
        $encoded = Encoder::encodeName('日本語.jp');

        static::assertNotEmpty($encoded);
    }

    public function testHyphenInLabel(): void
    {
        $encoded = Encoder::encodeName('my-host.example.com');

        static::assertNotEmpty($encoded);
    }

    public function testNumericLabel(): void
    {
        $encoded = Encoder::encodeName('123.456.example.com');

        static::assertNotEmpty($encoded);
    }

    public function testLabelWithUnderscore(): void
    {
        $encoded = Encoder::encodeName('_dmarc.example.com');

        static::assertNotEmpty($encoded);
    }

    public function testVeryLongValidName(): void
    {
        $labels = [];
        for ($i = 0; $i < 25; $i++) {
            $labels[] = str_repeat('a', 9);
        }

        $name = implode('.', $labels);

        $encoded = Encoder::encodeName($name);
        static::assertNotEmpty($encoded);
    }
}
