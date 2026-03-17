<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IRI\Exception\InvalidIRIException;
use Psl\IRI\Internal\IDNA;

use function explode;
use function str_repeat;

final class IDNATest extends TestCase
{
    public function testASCIIPassthrough(): void
    {
        static::assertSame('example.com', IDNA::toASCII('example.com'));
    }

    public function testUnicodeToASCII(): void
    {
        static::assertSame('xn--r8jz45g.jp', IDNA::toASCII('例え.jp'));
    }

    public function testASCIIToUnicode(): void
    {
        static::assertSame('例え.jp', IDNA::toUnicode('xn--r8jz45g.jp'));
    }

    public function testMixedLabels(): void
    {
        static::assertSame('www.xn--r8jz45g.jp', IDNA::toASCII('www.例え.jp'));
        static::assertSame('www.例え.jp', IDNA::toUnicode('www.xn--r8jz45g.jp'));
    }

    public function testMultipleUnicodeLabels(): void
    {
        static::assertSame('xn--mnchen-3ya.de', IDNA::toASCII('münchen.de'));
    }

    public function testRoundTrip(): void
    {
        $original = '例え.jp';
        $ascii = IDNA::toASCII($original);
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame($original, $unicode);
    }

    public function testInvalidLabelWithNoncharacter(): void
    {
        $this->expectException(InvalidIRIException::class);

        IDNA::toASCII("\u{FFFD}test.com");
    }

    public function testAlreadyPunycodeLabelPassthrough(): void
    {
        static::assertSame('xn--r8jz45g.jp', IDNA::toASCII('xn--r8jz45g.jp'));
    }

    public function testSingleCharacterASCIILabel(): void
    {
        static::assertSame('a.com', IDNA::toASCII('a.com'));
        static::assertSame('a.com', IDNA::toUnicode('a.com'));
    }

    public function testSingleUnicodeCharacterLabel(): void
    {
        $ascii = IDNA::toASCII('ü.com');
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame('ü.com', $unicode);
        static::assertStringStartsWith('xn--', explode('.', $ascii)[0]);
    }

    public function testLabelWithHyphenAtStartRejected(): void
    {
        $this->expectException(InvalidIRIException::class);

        IDNA::toASCII('-münchen.de');
    }

    public function testLabelWithHyphenAtEndRejected(): void
    {
        $this->expectException(InvalidIRIException::class);

        IDNA::toASCII('münchen-.de');
    }

    public function testVeryLongUnicodeLabel(): void
    {
        $label = str_repeat('あ', 30) . '.jp';
        $ascii = IDNA::toASCII($label);
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame($label, $unicode);
    }

    public function testDotSeparatedWithTrailingDot(): void
    {
        $ascii = IDNA::toASCII('例え.jp.');

        static::assertSame('xn--r8jz45g.jp.', $ascii);
    }

    public function testDotSeparatedWithTrailingDotUnicode(): void
    {
        $unicode = IDNA::toUnicode('xn--r8jz45g.jp.');

        static::assertSame('例え.jp.', $unicode);
    }

    public function testMultiplePunycodeLabels(): void
    {
        $ascii = IDNA::toASCII('münchen.münchen.de');
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame('münchen.münchen.de', $unicode);
    }

    public function testCyrillicDomain(): void
    {
        $ascii = IDNA::toASCII('пример.рф');
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame('пример.рф', $unicode);
    }

    public function testChineseDomain(): void
    {
        $ascii = IDNA::toASCII('中文.cn');
        $unicode = IDNA::toUnicode($ascii);

        static::assertSame('中文.cn', $unicode);
    }

    public function testASCIILabelWithHyphenInMiddle(): void
    {
        static::assertSame('my-site.com', IDNA::toASCII('my-site.com'));
    }
}
