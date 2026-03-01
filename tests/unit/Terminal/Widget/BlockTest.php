<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Block;
use Psl\Terminal\Widget\Border;
use Psl\Terminal\Widget\BorderStyle;
use Psl\Terminal\Widget\Line;
use Psl\Terminal\Widget\Paragraph;
use Psl\Terminal\Widget\Span;

final class BlockTest extends TestCase
{
    public function testRoundedBorder(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{256D}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{256E}", $buffer->get(9, 0)?->grapheme);
        static::assertSame("\u{2570}", $buffer->get(0, 4)?->grapheme);
        static::assertSame("\u{256F}", $buffer->get(9, 4)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 1)?->grapheme);
    }

    public function testPlainBorder(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(Border::plain())->render($area, Paragraph::new([]), $buffer);

        static::assertSame('+', $buffer->get(0, 0)?->grapheme);
        static::assertSame('-', $buffer->get(1, 0)?->grapheme);
        static::assertSame('|', $buffer->get(0, 1)?->grapheme);
    }

    public function testDoubleBorder(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(Border::double())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{2554}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2550}", $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{2551}", $buffer->get(0, 1)?->grapheme);
    }

    public function testThickBorder(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(Border::thick())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{250F}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2501}", $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{2503}", $buffer->get(0, 1)?->grapheme);
    }

    public function testTitlePlacement(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Block::new()
            ->title(' Chat ')
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        static::assertSame(' ', $buffer->get(1, 0)?->grapheme);
        static::assertSame('C', $buffer->get(2, 0)?->grapheme);
        static::assertSame('h', $buffer->get(3, 0)?->grapheme);
        static::assertSame('a', $buffer->get(4, 0)?->grapheme);
        static::assertSame('t', $buffer->get(5, 0)?->grapheme);
    }

    public function testTitleStyling(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $fg = Color\bright_cyan();

        Block::new()
            ->title(' Test ')
            ->titleStyle(foreground: $fg, style: Style\bold())
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        $cell = $buffer->get(2, 0);
        static::assertNotNull($cell);
        static::assertSame('T', $cell->grapheme);
        static::assertNotNull($cell->foreground);
    }

    public function testBorderColor(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        $fg = Color\bright_black();

        Block::new()->border(Border::rounded(color: $fg))->render($area, Paragraph::new([]), $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testInnerWidgetRendering(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Block::new()->border(Border::rounded())->render(
            $area,
            Paragraph::new([
                Line::new([Span::raw('Hello')]),
            ]),
            $buffer,
        );

        static::assertSame('H', $buffer->get(1, 1)?->grapheme);
        static::assertSame('e', $buffer->get(2, 1)?->grapheme);
    }

    public function testNoBorder(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(null)->render(
            $area,
            Paragraph::new([
                Line::new([Span::raw('Test')]),
            ]),
            $buffer,
        );

        static::assertSame('T', $buffer->get(0, 0)?->grapheme);
    }

    public function testMargin(): void
    {
        $buffer = new Buffer(20, 10);
        $area = new Rect(0, 0, 20, 10);

        Block::new()
            ->border(Border::rounded())
            ->margin(top: 1, left: 2)
            ->render(
                $area,
                Paragraph::new([
                    Line::new([Span::raw('Hi')]),
                ]),
                $buffer,
            );

        static::assertSame("\u{256D}", $buffer->get(2, 1)?->grapheme);
        static::assertSame('H', $buffer->get(3, 2)?->grapheme);
    }

    public function testBackground(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        $bg = Color\bright_black();

        Block::new()
            ->border(Border::rounded())
            ->background($bg)
            ->render($area, Paragraph::new([]), $buffer);

        $cell = $buffer->get(1, 1);
        static::assertNotNull($cell);
        static::assertNotNull($cell->background);

        $cell = $buffer->get(2, 2);
        static::assertNotNull($cell);
        static::assertNotNull($cell->background);
    }

    public function testPerSideBorderTopOnly(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(
            new Border(BorderStyle::Rounded, top: true, right: false, bottom: false, left: false),
        )->render(
            $area,
            Paragraph::new([
                Line::new([Span::raw('Test')]),
            ]),
            $buffer,
        );

        static::assertSame("\u{2500}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(5, 0)?->grapheme);
        static::assertSame('T', $buffer->get(0, 1)?->grapheme);
    }

    public function testPerSideBorderLeftRight(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 10, 5);

        Block::new()->border(
            new Border(BorderStyle::Rounded, top: false, right: true, bottom: false, left: true),
        )->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{2502}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 2)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(9, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(9, 2)?->grapheme);
        static::assertSame(' ', $buffer->get(5, 0)?->grapheme);
    }

    public function testInnerAreaWithMargin(): void
    {
        $area = new Rect(0, 0, 20, 10);

        $block = Block::new()
            ->border(Border::rounded())
            ->margin(top: 1, right: 1, bottom: 1, left: 1)
            ->padding(top: 1, left: 1);

        $inner = $block->innerArea($area);

        static::assertSame(3, $inner->x);
        static::assertSame(3, $inner->y);
        static::assertSame(20 - 3 - 2, $inner->width);
        static::assertSame(10 - 3 - 2, $inner->height);
    }
}
