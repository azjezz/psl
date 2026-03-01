<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Sparkline;

final class SparklineTest extends TestCase
{
    public function testBasicBlockMapping(): void
    {
        $buffer = new Buffer(3, 1);
        $area = new Rect(0, 0, 3, 1);

        Sparkline::new([0.0, 0.5, 1.0])->render($area, $buffer);

        static::assertSame("\u{2581}", $buffer->get(0, 0)?->grapheme); // 0.0 → ▁
        static::assertSame("\u{2588}", $buffer->get(2, 0)?->grapheme); // 1.0 → █
    }

    public function testRightAlignedWhenDataShorterThanWidth(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        Sparkline::new([0.0, 1.0])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(1, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(2, 0)?->grapheme);
        static::assertSame("\u{2581}", $buffer->get(3, 0)?->grapheme); // 0.0
        static::assertSame("\u{2588}", $buffer->get(4, 0)?->grapheme); // 1.0
    }

    public function testDataTruncatedFromLeft(): void
    {
        $buffer = new Buffer(3, 1);
        $area = new Rect(0, 0, 3, 1);

        Sparkline::new([0.0, 0.25, 0.5, 0.75, 1.0])->render($area, $buffer);

        static::assertSame("\u{2585}", $buffer->get(0, 0)?->grapheme); // 0.5 → round(3.5)=4 → ▅
        static::assertSame("\u{2588}", $buffer->get(2, 0)?->grapheme); // 1.0 → █
    }

    public function testEmptyDataRendersNothing(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        Sparkline::new([])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(5, 0)?->grapheme);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 0, 0);

        Sparkline::new([0.5, 0.5])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testStyleApplied(): void
    {
        $buffer = new Buffer(3, 1);
        $area = new Rect(0, 0, 3, 1);

        $fg = Color\bright_cyan();

        Sparkline::new([0.5, 0.5, 0.5])->style(foreground: $fg)->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }
}
