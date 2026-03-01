<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Span;
use Psl\Terminal\Widget\Table;

final class TableTest extends TestCase
{
    public function testHeadersRenderOnFirstRow(): void
    {
        $buffer = new Buffer(30, 10);
        $area = new Rect(0, 0, 30, 10);

        Table::new()
            ->headers(['PID', 'NAME'])
            ->widths([8, 20])
            ->rows([])
            ->render($area, $buffer);

        static::assertSame('P', $buffer->get(0, 0)?->grapheme);
        static::assertSame('I', $buffer->get(1, 0)?->grapheme);
        static::assertSame('D', $buffer->get(2, 0)?->grapheme);

        static::assertSame('N', $buffer->get(8, 0)?->grapheme);
        static::assertSame('A', $buffer->get(9, 0)?->grapheme);
        static::assertSame('M', $buffer->get(10, 0)?->grapheme);
        static::assertSame('E', $buffer->get(11, 0)?->grapheme);
    }

    public function testSeparatorOnSecondRow(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->headers(['COL'])
            ->widths([20])
            ->rows([])
            ->render($area, $buffer);

        static::assertSame("\u{2500}", $buffer->get(0, 1)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(10, 1)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(19, 1)?->grapheme);
    }

    public function testDataRowsRenderBelowSeparator(): void
    {
        $buffer = new Buffer(20, 10);
        $area = new Rect(0, 0, 20, 10);

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
            ])
            ->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 2)?->grapheme);
        static::assertSame('B', $buffer->get(0, 3)?->grapheme);
    }

    public function testColumnPadding(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->headers(['A', 'B'])
            ->widths([10, 10])
            ->rows([
                [Span::raw('Hi'), Span::raw('Lo')],
            ])
            ->render($area, $buffer);

        static::assertSame('H', $buffer->get(0, 2)?->grapheme);
        static::assertSame('i', $buffer->get(1, 2)?->grapheme);
        static::assertSame(' ', $buffer->get(2, 2)?->grapheme);
        static::assertSame('L', $buffer->get(10, 2)?->grapheme);
        static::assertSame('o', $buffer->get(11, 2)?->grapheme);
    }

    public function testHighlightAppliesStyle(): void
    {
        $buffer = new Buffer(20, 10);
        $area = new Rect(0, 0, 20, 10);

        $bg = Color\ansi256(236);

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->rows([
                [Span::raw('First')],
                [Span::raw('Second')],
            ])
            ->highlight(1)
            ->highlightStyle(foreground: Color\bright_white(), background: $bg, style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 3);
        static::assertNotNull($cell);
        static::assertSame('S', $cell->grapheme);
        static::assertNotNull($cell->foreground);
        static::assertNotNull($cell->background);
    }

    public function testScrollSkipsRows(): void
    {
        $buffer = new Buffer(20, 4);
        $area = new Rect(0, 0, 20, 4);

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
                [Span::raw('Charlie')],
            ])
            ->scroll(1)
            ->render($area, $buffer);

        static::assertSame('B', $buffer->get(0, 2)?->grapheme);
        static::assertSame('C', $buffer->get(0, 3)?->grapheme);
    }

    public function testHeaderStyleApplied(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $fg = Color\bright_cyan();

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->headerStyle(foreground: $fg, style: Style\bold())
            ->rows([])
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('N', $cell->grapheme);
        static::assertNotNull($cell->foreground);
    }

    public function testNoHeadersNoSeparator(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->widths([20])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
            ])
            ->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(0, 1)?->grapheme);

        static::assertSame(' ', $buffer->get(0, 2)?->grapheme);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 0, 0);

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->rows([[Span::raw('test')]])
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testNoHighlightRendersNoStyledRows(): void
    {
        $buffer = new Buffer(20, 10);
        $area = new Rect(0, 0, 20, 10);

        Table::new()
            ->headers(['NAME'])
            ->widths([20])
            ->rows([
                [Span::raw('First')],
                [Span::raw('Second')],
            ])
            ->render($area, $buffer);

        $cell = $buffer->get(0, 2);
        static::assertNotNull($cell);
        static::assertSame('F', $cell->grapheme);
        static::assertNull($cell->background);

        $cell = $buffer->get(0, 3);
        static::assertNotNull($cell);
        static::assertSame('S', $cell->grapheme);
        static::assertNull($cell->background);
    }

    public function testHeaderStyleModifier(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->headers(['Name', 'Age'])
            ->widths([10, 10])
            ->headerStyle(style: Style\bold())
            ->rows([[Span::raw('Alice'), Span::raw('30')]])
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testHighlightStyleModifier(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->headers(['Name'])
            ->widths([20])
            ->highlightStyle(style: Style\bold())
            ->highlight(0)
            ->rows([[Span::raw('Alice')]])
            ->render($area, $buffer);

        $cell = $buffer->get(0, 2);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testEmptyAreaDoesNotCorruptBuffer(): void
    {
        $buffer = new Buffer(10, 5);
        $buffer->set(0, 0, new \Psl\Terminal\Cell('X'));
        $area = new Rect(0, 0, 0, 0);

        Table::new()
            ->headers(['Name'])
            ->widths([10])
            ->rows([[Span::raw('Alice')]])
            ->render($area, $buffer);

        static::assertSame('X', $buffer->get(0, 0)?->grapheme);
    }

    public function testHeaderAndSeparator(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Table::new()
            ->headers(['Col1', 'Col2'])
            ->widths([5, 5])
            ->rows([[Span::raw('A'), Span::raw('B')]])
            ->render($area, $buffer);

        static::assertSame('C', $buffer->get(0, 0)?->grapheme);
        static::assertSame('C', $buffer->get(5, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(0, 1)?->grapheme);
    }

    public function testHighlightBackground(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        $bg = Color\blue();

        Table::new()
            ->headers(['Name'])
            ->widths([10])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
            ])
            ->highlight(0)
            ->highlightStyle(background: $bg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 2);
        static::assertNotNull($cell);
        static::assertSame($bg, $cell->background);

        $cell = $buffer->get(9, 2);
        static::assertNotNull($cell);
        static::assertSame($bg, $cell->background);

        $unhighlighted = $buffer->get(0, 3);
        static::assertNotNull($unhighlighted);
        static::assertNull($unhighlighted->background);
    }

    public function testHighlightOverridesFg(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        $green = Color\green();

        Table::new()
            ->headers(['Name'])
            ->widths([10])
            ->rows([[Span::raw('Alice')]])
            ->highlight(0)
            ->highlightStyle(foreground: $green)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 2);
        static::assertNotNull($cell);
        static::assertSame($green, $cell->foreground);
    }

    public function testTableScrollClamp(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Table::new()
            ->headers(['Name'])
            ->widths([10])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
                [Span::raw('Charlie')],
            ])
            ->scroll(100)
            ->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 2)?->grapheme);
    }

    public function testRowClipsToBottom(): void
    {
        $buffer = new Buffer(10, 4);
        $area = new Rect(0, 0, 10, 4);

        Table::new()
            ->headers(['Name'])
            ->widths([10])
            ->rows([
                [Span::raw('Alice')],
                [Span::raw('Bob')],
                [Span::raw('Charlie')],
                [Span::raw('Dave')],
            ])
            ->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 2)?->grapheme);
        static::assertSame('B', $buffer->get(0, 3)?->grapheme);
    }
}
