<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Scrollbar;

final class ScrollbarTest extends TestCase
{
    public function testFullThumbWhenContentFitsViewport(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(3)
            ->viewportLength(5)
            ->position(0)
            ->render($area, $buffer);

        for ($i = 0; $i < 5; $i++) {
            static::assertSame("\u{2503}", $buffer->get(0, $i)?->grapheme, "Row {$i}");
        }
    }

    public function testThumbAtTop(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame("\u{2503}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 9)?->grapheme);
    }

    public function testThumbAtBottom(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(90)
            ->render($area, $buffer);

        static::assertSame("\u{2502}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2503}", $buffer->get(0, 9)?->grapheme);
    }

    public function testThumbStyleApplied(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        $fg = Color\bright_cyan();

        Scrollbar::new()
            ->contentLength(5)
            ->viewportLength(5)
            ->position(0)
            ->thumbStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testTrackStyleApplied(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        $fg = Color\bright_black();

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->trackStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 9);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(1, 1);
        $area = new Rect(0, 0, 0, 0);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testZeroContentRendersFullThumb(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(0)
            ->viewportLength(5)
            ->position(0)
            ->render($area, $buffer);

        for ($i = 0; $i < 5; $i++) {
            static::assertSame("\u{2503}", $buffer->get(0, $i)?->grapheme, "Row {$i}");
        }
    }
}
