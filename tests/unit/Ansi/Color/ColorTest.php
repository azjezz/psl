<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi\Color;

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

        Color\ansi256(-1);
    }

    public function testAnsi256OverflowThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

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

        Color\rgb(-1, 0, 0);
    }

    public function testRgbOverflowGreenThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

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

        Color\hex('xyz');
    }

    public function testGetValueThrowsForRgb(): void
    {
        $color = Color\rgb(255, 0, 0);

        $this->expectException(Exception\LogicException::class);

        $color->getValue();
    }

    public function testGetRedThrowsForBasic(): void
    {
        $color = Color\red();

        $this->expectException(Exception\LogicException::class);

        $color->getRed();
    }

    public function testGetGreenThrowsForAnsi256(): void
    {
        $color = Color\ansi256(196);

        $this->expectException(Exception\LogicException::class);

        $color->getGreen();
    }

    public function testGetBlueThrowsForBasic(): void
    {
        $color = Color\red();

        $this->expectException(Exception\LogicException::class);

        $color->getBlue();
    }
}
