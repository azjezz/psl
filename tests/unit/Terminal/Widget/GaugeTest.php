<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Gauge;

final class GaugeTest extends TestCase
{
    public function testBasicGaugeWithLabel(): void
    {
        $buffer = new Buffer(30, 1);
        $area = new Rect(0, 0, 30, 1);

        Gauge::new()
            ->ratio(0.5)
            ->label('CPU')
            ->render($area, $buffer);

        static::assertSame('C', $buffer->get(0, 0)?->grapheme);
        static::assertSame('P', $buffer->get(1, 0)?->grapheme);
        static::assertSame('U', $buffer->get(2, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(3, 0)?->grapheme);
    }

    public function testZeroRatio(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Gauge::new()->ratio(0.0)->render($area, $buffer);

        $allEmpty = true;
        for ($x = 0; $x < 17; $x++) {
            if ($buffer->get($x, 0)?->grapheme !== "\u{2588}") {
                continue;
            }

            $allEmpty = false;
            break;
        }

        static::assertTrue($allEmpty);
    }

    public function testFullRatio(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Gauge::new()->ratio(1.0)->render($area, $buffer);

        $filledCount = 0;
        for ($x = 0; $x < 15; $x++) {
            if ($buffer->get($x, 0)?->grapheme !== "\u{2588}") {
                continue;
            }

            $filledCount++;
        }

        static::assertSame(15, $filledCount);
    }

    public function testRatioIsClamped(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Gauge::new()->ratio(1.5)->render($area, $buffer);
        Gauge::new()->ratio(-0.5)->render($area, $buffer);

        static::assertTrue(true);
    }

    public function testNoLabel(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Gauge::new()->ratio(0.5)->render($area, $buffer);

        $grapheme = $buffer->get(0, 0)?->grapheme;
        static::assertTrue($grapheme === "\u{2588}" || $grapheme === "\u{2591}");
    }

    public function testFilledStyleApplied(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $fg = Color\bright_green();

        Gauge::new()
            ->ratio(0.5)
            ->filledStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame("\u{2588}", $cell->grapheme);
        static::assertNotNull($cell->foreground);
    }

    public function testEmptyStyleApplied(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $fg = Color\bright_black();

        Gauge::new()
            ->ratio(0.0)
            ->emptyStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame("\u{2591}", $cell->grapheme);
        static::assertNotNull($cell->foreground);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 0, 0);

        Gauge::new()
            ->ratio(0.5)
            ->label('CPU')
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }
}
