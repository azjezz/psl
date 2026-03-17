<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
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

        $fg = Ansi\foreground(Color\bright_cyan());

        Scrollbar::new()
            ->contentLength(5)
            ->viewportLength(5)
            ->position(0)
            ->thumbStyle($fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testTrackStyleApplied(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        $fg = Ansi\foreground(Color\bright_black());

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->trackStyle($fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 9);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
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

    public function testThumbStyleModifier(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(5)
            ->viewportLength(5)
            ->position(0)
            ->thumbStyle(Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testTrackStyleModifier(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->trackStyle(Style\italic())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 9);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testThumbSizeAndPosition(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(50)
            ->viewportLength(10)
            ->position(20)
            ->render($area, $buffer);

        $thumbCount = 0;
        $trackCount = 0;
        $firstThumb = -1;
        for ($i = 0; $i < 10; $i++) {
            $g = $buffer->get(0, $i)?->grapheme;
            if ($g === "\u{2503}") {
                $thumbCount++;
                if ($firstThumb === -1) {
                    $firstThumb = $i;
                }
            } elseif ($g === "\u{2502}") {
                $trackCount++;
            }
        }

        static::assertSame(10, $thumbCount + $trackCount);
        static::assertGreaterThan(0, $thumbCount);
        static::assertGreaterThan(0, $trackCount);
        static::assertGreaterThan(0, $firstThumb);
    }

    public function testContentLengthClamp(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(0)
            ->viewportLength(5)
            ->position(0)
            ->render($area, $buffer);

        for ($i = 0; $i < 5; $i++) {
            static::assertSame("\u{2503}", $buffer->get(0, $i)?->grapheme);
        }
    }

    public function testViewportLengthClamp(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(5)
            ->viewportLength(0)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame("\u{2503}", $buffer->get(0, 0)?->grapheme);
    }

    public function testPositionClamp(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame("\u{2503}", $buffer->get(0, 0)?->grapheme);
    }

    public function testTrackDoesNotExceedArea(): void
    {
        $buffer = new Buffer(1, 3);
        $area = new Rect(0, 0, 1, 3);

        Scrollbar::new()
            ->contentLength(3)
            ->viewportLength(3)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame("\u{2503}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2503}", $buffer->get(0, 2)?->grapheme);
        static::assertNull($buffer->get(0, 3));
    }

    public function testThumbExactBounds(): void
    {
        $buffer = new Buffer(1, 10);
        $area = new Rect(0, 0, 1, 10);

        Scrollbar::new()
            ->contentLength(100)
            ->viewportLength(10)
            ->position(0)
            ->render($area, $buffer);

        static::assertSame("\u{2503}", $buffer->get(0, 0)?->grapheme);

        $lastThumb = -1;
        for ($i = 0; $i < 10; $i++) {
            if ($buffer->get(0, $i)?->grapheme !== "\u{2503}") {
                continue;
            }

            $lastThumb = $i;
        }

        static::assertGreaterThanOrEqual(0, $lastThumb);
        if ($lastThumb < 9) {
            static::assertSame("\u{2502}", $buffer->get(0, $lastThumb + 1)?->grapheme);
        }
    }

    public function testContentEqualViewport(): void
    {
        $buffer = new Buffer(1, 5);
        $area = new Rect(0, 0, 1, 5);

        Scrollbar::new()
            ->contentLength(5)
            ->viewportLength(5)
            ->position(0)
            ->render($area, $buffer);

        for ($i = 0; $i < 5; $i++) {
            static::assertSame("\u{2503}", $buffer->get(0, $i)?->grapheme);
        }
    }
}
