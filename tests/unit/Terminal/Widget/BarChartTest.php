<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\BarChart;

final class BarChartTest extends TestCase
{
    public function testBasicBar(): void
    {
        $buffer = new Buffer(3, 6);
        $area = new Rect(0, 0, 3, 6);

        BarChart::new()
            ->data([['A', 1.0]])
            ->barWidth(3)
            ->barGap(0)
            ->render($area, $buffer);

        for ($row = 0; $row < 5; $row++) {
            static::assertSame("\u{2588}", $buffer->get(0, $row)?->grapheme, "Row {$row} col 0");
            static::assertSame("\u{2588}", $buffer->get(1, $row)?->grapheme, "Row {$row} col 1");
            static::assertSame("\u{2588}", $buffer->get(2, $row)?->grapheme, "Row {$row} col 2");
        }

        static::assertSame('A', $buffer->get(1, 5)?->grapheme);
    }

    public function testHalfBar(): void
    {
        $buffer = new Buffer(3, 5);
        $area = new Rect(0, 0, 3, 5);

        BarChart::new()
            ->data([['X', 0.5]])
            ->barWidth(3)
            ->barGap(0)
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme); // empty
        static::assertSame(' ', $buffer->get(0, 1)?->grapheme); // empty
        static::assertSame("\u{2588}", $buffer->get(0, 2)?->grapheme); // filled
        static::assertSame("\u{2588}", $buffer->get(0, 3)?->grapheme); // filled

        static::assertSame('X', $buffer->get(1, 4)?->grapheme);
    }

    public function testMultipleBars(): void
    {
        $buffer = new Buffer(7, 4);
        $area = new Rect(0, 0, 7, 4);

        BarChart::new()
            ->data([['A', 1.0], ['B', 0.0]])
            ->barWidth(3)
            ->barGap(1)
            ->render($area, $buffer);

        static::assertSame("\u{2588}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2588}", $buffer->get(0, 2)?->grapheme);
        static::assertSame(' ', $buffer->get(4, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(4, 2)?->grapheme);
        static::assertSame('A', $buffer->get(1, 3)?->grapheme);
        static::assertSame('B', $buffer->get(5, 3)?->grapheme);
    }

    public function testEmptyDataRendersNothing(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        BarChart::new()->data([])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testTooSmallAreaRendersNothing(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        BarChart::new()->data([['A', 0.5]])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testBarStyleApplied(): void
    {
        $buffer = new Buffer(3, 3);
        $area = new Rect(0, 0, 3, 3);

        $fg = Color\bright_cyan();

        BarChart::new()
            ->data([['X', 1.0]])
            ->barWidth(3)
            ->barGap(0)
            ->barStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testLabelStyleApplied(): void
    {
        $buffer = new Buffer(3, 3);
        $area = new Rect(0, 0, 3, 3);

        $fg = Color\bright_white();

        BarChart::new()
            ->data([['X', 0.5]])
            ->barWidth(3)
            ->barGap(0)
            ->labelStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(1, 2);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testBarStyleWithModifier(): void
    {
        $buffer = new Buffer(3, 3);
        $area = new Rect(0, 0, 3, 3);

        BarChart::new()
            ->data([['A', 1.0]])
            ->barWidth(3)
            ->barGap(0)
            ->barStyle(foreground: Color\red(), style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame("\u{2588}", $cell->grapheme);
        static::assertNotNull($cell->foreground);
    }

    public function testLabelStyleWithModifier(): void
    {
        $buffer = new Buffer(3, 3);
        $area = new Rect(0, 0, 3, 3);

        BarChart::new()
            ->data([['X', 0.5]])
            ->barWidth(3)
            ->barGap(0)
            ->labelStyle(foreground: Color\white(), style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 2);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testBarsClipWhenExceedingAreaWidth(): void
    {
        $buffer = new Buffer(5, 3);
        $area = new Rect(0, 0, 5, 3);

        BarChart::new()
            ->data([['A', 1.0], ['B', 1.0], ['C', 1.0]])
            ->barWidth(3)
            ->barGap(1)
            ->render($area, $buffer);

        static::assertSame("\u{2588}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2588}", $buffer->get(4, 0)?->grapheme);
    }

    public function testBarColumnClipsToAreaRight(): void
    {
        $buffer = new Buffer(4, 3);
        $area = new Rect(0, 0, 4, 3);

        BarChart::new()
            ->data([['A', 1.0]])
            ->barWidth(6)
            ->barGap(0)
            ->render($area, $buffer);

        static::assertSame("\u{2588}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2588}", $buffer->get(3, 0)?->grapheme);
        static::assertNull($buffer->get(4, 0));
    }
}
