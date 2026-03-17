<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Alignment;
use Psl\Terminal\Widget\Line;
use Psl\Terminal\Widget\Paragraph;
use Psl\Terminal\Widget\Span;
use Psl\Terminal\Widget\Wrap;

final class ParagraphTest extends TestCase
{
    public function testSingleLineRender(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Hello')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('H', $buffer->get(0, 0)?->grapheme);
        static::assertSame('e', $buffer->get(1, 0)?->grapheme);
        static::assertSame('l', $buffer->get(2, 0)?->grapheme);
        static::assertSame('l', $buffer->get(3, 0)?->grapheme);
        static::assertSame('o', $buffer->get(4, 0)?->grapheme);
    }

    public function testMultiLineRender(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Line 1')]),
            Line::new([Span::raw('Line 2')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('L', $buffer->get(0, 0)?->grapheme);
        static::assertSame('L', $buffer->get(0, 1)?->grapheme);
    }

    public function testWordWrap(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Hello World Test')]),
        ])->wrap(Wrap::Word);

        $paragraph->render($area, $buffer);

        static::assertSame('H', $buffer->get(0, 0)?->grapheme);
        static::assertSame('W', $buffer->get(0, 1)?->grapheme);
    }

    public function testCharWrap(): void
    {
        $buffer = new Buffer(5, 5);
        $area = new Rect(0, 0, 5, 5);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('ABCDEFGHIJ')]),
        ])->wrap(Wrap::Char);

        $paragraph->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('E', $buffer->get(4, 0)?->grapheme);
        static::assertSame('F', $buffer->get(0, 1)?->grapheme);
    }

    public function testScrollOffset(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Line 0')]),
            Line::new([Span::raw('Line 1')]),
            Line::new([Span::raw('Line 2')]),
            Line::new([Span::raw('Line 3')]),
        ])->scroll(2);

        $paragraph->render($area, $buffer);

        static::assertSame('L', $buffer->get(0, 0)?->grapheme);
        static::assertSame('2', $buffer->get(5, 0)?->grapheme);
    }

    public function testRightAlignment(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Hi')]),
        ])->alignment(Alignment::Right);

        $paragraph->render($area, $buffer);

        static::assertSame('H', $buffer->get(18, 0)?->grapheme);
        static::assertSame('i', $buffer->get(19, 0)?->grapheme);
    }

    public function testEmptyArea(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 0, 0);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Test')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testCenterAlignment(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Hi')]),
        ])->alignment(Alignment::Center);

        $paragraph->render($area, $buffer);

        static::assertSame('H', $buffer->get(9, 0)?->grapheme);
        static::assertSame('i', $buffer->get(10, 0)?->grapheme);
    }

    public function testTextClipsToAreaWidth(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 3, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('ABCDEF')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(1, 0)?->grapheme);
        static::assertSame('C', $buffer->get(2, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(3, 0)?->grapheme);
    }

    public function testWideCharacterContinuationCell(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('漢字')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('漢', $buffer->get(0, 0)?->grapheme);
        static::assertSame('', $buffer->get(1, 0)?->grapheme);
        static::assertSame('字', $buffer->get(2, 0)?->grapheme);
        static::assertSame('', $buffer->get(3, 0)?->grapheme);
    }

    public function testScrollClampsToZero(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Line 0')]),
            Line::new([Span::raw('Line 1')]),
        ])->scroll(0);

        $paragraph->render($area, $buffer);

        static::assertSame('L', $buffer->get(0, 0)?->grapheme);
        static::assertSame('0', $buffer->get(5, 0)?->grapheme);
    }

    public function testMaxScrollClamp(): void
    {
        $buffer = new Buffer(20, 3);
        $area = new Rect(0, 0, 20, 3);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Line 0')]),
            Line::new([Span::raw('Line 1')]),
            Line::new([Span::raw('Line 2')]),
        ])->scroll(100);

        $paragraph->render($area, $buffer);

        static::assertSame('0', $buffer->get(5, 0)?->grapheme);
        static::assertSame('1', $buffer->get(5, 1)?->grapheme);
        static::assertSame('2', $buffer->get(5, 2)?->grapheme);
    }

    public function testRenderClipsToBottom(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Line 0')]),
            Line::new([Span::raw('Line 1')]),
            Line::new([Span::raw('Line 2')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('0', $buffer->get(5, 0)?->grapheme);
        static::assertSame('1', $buffer->get(5, 1)?->grapheme);
    }

    public function testRightAlignmentMaxvaClamp(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('ABCDEF')]),
        ])->alignment(Alignment::Right);

        $paragraph->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
    }

    public function testWideCharContinuationInParagraph(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('漢字')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('漢', $buffer->get(0, 0)?->grapheme);
        static::assertSame('', $buffer->get(1, 0)?->grapheme);
        static::assertSame('字', $buffer->get(2, 0)?->grapheme);
        static::assertSame('', $buffer->get(3, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(4, 0)?->grapheme);
    }

    public function testEmptyAreaDoesNotCorruptBuffer(): void
    {
        $buffer = new Buffer(10, 5);
        $buffer->set(0, 0, new Cell('X'));
        $area = new Rect(0, 0, 0, 0);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('Test')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('X', $buffer->get(0, 0)?->grapheme);
    }

    public function testTextClipsToAreaRightExact(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 3, 1);

        $paragraph = Paragraph::new([
            Line::new([Span::raw('ABCDEF')]),
        ]);

        $paragraph->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(1, 0)?->grapheme);
        static::assertSame('C', $buffer->get(2, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(3, 0)?->grapheme);
    }
}
