<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit\Color;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Color\ColorKind;
use Psl\Ansi\Exception;

final class ColorTest extends TestCase
{
    public function testBlack(): void
    {
        $color = Color\black();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(30, $color->getValue());
    }

    public function testRed(): void
    {
        $color = Color\red();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(31, $color->getValue());
    }

    public function testGreen(): void
    {
        $color = Color\green();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(32, $color->getValue());
    }

    public function testYellow(): void
    {
        $color = Color\yellow();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(33, $color->getValue());
    }

    public function testBlue(): void
    {
        $color = Color\blue();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(34, $color->getValue());
    }

    public function testMagenta(): void
    {
        $color = Color\magenta();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(35, $color->getValue());
    }

    public function testCyan(): void
    {
        $color = Color\cyan();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(36, $color->getValue());
    }

    public function testWhite(): void
    {
        $color = Color\white();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(37, $color->getValue());
    }

    public function testBrightBlack(): void
    {
        $color = Color\bright_black();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(90, $color->getValue());
    }

    public function testBrightRed(): void
    {
        $color = Color\bright_red();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(91, $color->getValue());
    }

    public function testBrightGreen(): void
    {
        $color = Color\bright_green();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(92, $color->getValue());
    }

    public function testBrightYellow(): void
    {
        $color = Color\bright_yellow();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(93, $color->getValue());
    }

    public function testBrightBlue(): void
    {
        $color = Color\bright_blue();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(94, $color->getValue());
    }

    public function testBrightMagenta(): void
    {
        $color = Color\bright_magenta();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(95, $color->getValue());
    }

    public function testBrightCyan(): void
    {
        $color = Color\bright_cyan();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(96, $color->getValue());
    }

    public function testBrightWhite(): void
    {
        $color = Color\bright_white();

        static::assertSame(ColorKind::Basic, $color->getKind());
        static::assertSame(97, $color->getValue());
    }

    public function testAnsi256Zero(): void
    {
        $color = Color\ansi256(0);

        static::assertSame(ColorKind::Ansi256, $color->getKind());
        static::assertSame(0, $color->getValue());
    }

    public function testAnsi256Mid(): void
    {
        $color = Color\ansi256(127);

        static::assertSame(ColorKind::Ansi256, $color->getKind());
        static::assertSame(127, $color->getValue());
    }

    public function testAnsi256Max(): void
    {
        $color = Color\ansi256(255);

        static::assertSame(ColorKind::Ansi256, $color->getKind());
        static::assertSame(255, $color->getValue());
    }

    public function testAnsi256NegativeThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an ANSI-256 color code between 0 and 255, got -1.');

        Color\ansi256(-1);
    }

    public function testAnsi256OverflowThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an ANSI-256 color code between 0 and 255, got 256.');

        Color\ansi256(256);
    }

    public function testRgbBlack(): void
    {
        $color = Color\rgb(0, 0, 0);

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(0, $color->getRed());
        static::assertSame(0, $color->getGreen());
        static::assertSame(0, $color->getBlue());
    }

    public function testRgbWhite(): void
    {
        $color = Color\rgb(255, 255, 255);

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(255, $color->getRed());
        static::assertSame(255, $color->getGreen());
        static::assertSame(255, $color->getBlue());
    }

    public function testRgbCustom(): void
    {
        $color = Color\rgb(128, 64, 32);

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(128, $color->getRed());
        static::assertSame(64, $color->getGreen());
        static::assertSame(32, $color->getBlue());
    }

    public function testRgbNegativeRedThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected red component between 0 and 255, got -1.');

        Color\rgb(-1, 0, 0);
    }

    public function testRgbOverflowGreenThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected green component between 0 and 255, got 256.');

        Color\rgb(0, 256, 0);
    }

    public function testHexSixChar(): void
    {
        $color = Color\hex('ff0000');

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(255, $color->getRed());
        static::assertSame(0, $color->getGreen());
        static::assertSame(0, $color->getBlue());
    }

    public function testHexThreeChar(): void
    {
        $color = Color\hex('f00');

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(255, $color->getRed());
        static::assertSame(0, $color->getGreen());
        static::assertSame(0, $color->getBlue());
    }

    public function testHexWithPrefix(): void
    {
        $color = Color\hex('#ff0000');

        static::assertSame(ColorKind::Rgb, $color->getKind());
        static::assertSame(255, $color->getRed());
        static::assertSame(0, $color->getGreen());
        static::assertSame(0, $color->getBlue());
    }

    public function testHexInvalidThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a valid hex color string, got "xxyyzz".');

        Color\hex('xyz');
    }

    public function testGetValueThrowsForRgb(): void
    {
        $color = Color\rgb(255, 0, 0);

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve value from an RGB color.');

        $color->getValue();
    }

    public function testGetRedThrowsForBasic(): void
    {
        $color = Color\red();

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve red component from a non-RGB color.');

        $color->getRed();
    }

    public function testGetGreenThrowsForAnsi256(): void
    {
        $color = Color\ansi256(196);

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve green component from a non-RGB color.');

        $color->getGreen();
    }

    public function testGetBlueThrowsForBasic(): void
    {
        $color = Color\red();

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve blue component from a non-RGB color.');

        $color->getBlue();
    }

    public function testRgbNegativeBlueThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected blue component between 0 and 255, got -1.');

        Color\rgb(0, 0, -1);
    }

    public function testRgbOverflowBlueThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected blue component between 0 and 255, got 256.');

        Color\rgb(0, 0, 256);
    }

    public function testRgbOverflowRedThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected red component between 0 and 255, got 256.');

        Color\rgb(256, 0, 0);
    }

    public function testRgbNegativeGreenThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected green component between 0 and 255, got -1.');

        Color\rgb(0, -1, 0);
    }

    public function testRgbComponentsAreExact(): void
    {
        $color = Color\rgb(1, 2, 3);

        static::assertSame(1, $color->getRed());
        static::assertSame(2, $color->getGreen());
        static::assertSame(3, $color->getBlue());
    }

    public function testBasicGetValueIsExact(): void
    {
        $color = Color\Color::basic(5);

        static::assertSame(5, $color->getValue());
    }

    public function testAnsi256GetValueIsExact(): void
    {
        $color = Color\ansi256(42);

        static::assertSame(42, $color->getValue());
    }

    public function testGetRedThrowsForAnsi256(): void
    {
        $color = Color\ansi256(100);

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve red component from a non-RGB color.');

        $color->getRed();
    }

    public function testGetBlueThrowsForAnsi256(): void
    {
        $color = Color\ansi256(100);

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve blue component from a non-RGB color.');

        $color->getBlue();
    }

    public function testGetGreenThrowsForBasic(): void
    {
        $color = Color\Color::basic(30);

        $this->expectException(Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot retrieve green component from a non-RGB color.');

        $color->getGreen();
    }

    public function testEqualsWithSameRgbColors(): void
    {
        $a = Color\rgb(10, 20, 30);
        $b = Color\rgb(10, 20, 30);

        static::assertTrue($a->equals($b));
    }

    public function testEqualsWithDifferentRgbColors(): void
    {
        $a = Color\rgb(10, 20, 30);
        $b = Color\rgb(10, 20, 31);

        static::assertFalse($a->equals($b));
    }

    public function testEqualsWithDifferentKinds(): void
    {
        $a = Color\Color::basic(0);
        $b = Color\rgb(0, 0, 0);

        static::assertFalse($a->equals($b));
    }

    public function testEqualsWithSameBasicColors(): void
    {
        $a = Color\Color::basic(30);
        $b = Color\Color::basic(30);

        static::assertTrue($a->equals($b));
    }

    public function testEqualsWithDifferentBasicColors(): void
    {
        $a = Color\Color::basic(30);
        $b = Color\Color::basic(31);

        static::assertFalse($a->equals($b));
    }

    public function testEqualsWithSameAnsi256Colors(): void
    {
        $a = Color\ansi256(42);
        $b = Color\ansi256(42);

        static::assertTrue($a->equals($b));
    }

    public function testEqualsWithDifferentAnsi256Colors(): void
    {
        $a = Color\ansi256(42);
        $b = Color\ansi256(43);

        static::assertFalse($a->equals($b));
    }
}
