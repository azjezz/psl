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

    public function testPaddingDefaultsAreZero(): void
    {
        $area = new Rect(0, 0, 10, 10);

        $withExplicit = Block::new()->border(Border::rounded())->padding(0, 0, 0, 0);
        $withDefaults = Block::new()->border(Border::rounded())->padding();

        $inner1 = $withExplicit->innerArea($area);
        $inner2 = $withDefaults->innerArea($area);

        static::assertSame($inner1->x, $inner2->x);
        static::assertSame($inner1->y, $inner2->y);
        static::assertSame($inner1->width, $inner2->width);
        static::assertSame($inner1->height, $inner2->height);
    }

    public function testMarginDefaultsAreZero(): void
    {
        $area = new Rect(0, 0, 10, 10);

        $withExplicit = Block::new()->border(Border::rounded())->margin(0, 0, 0, 0);
        $withDefaults = Block::new()->border(Border::rounded())->margin();

        $inner1 = $withExplicit->innerArea($area);
        $inner2 = $withDefaults->innerArea($area);

        static::assertSame($inner1->x, $inner2->x);
        static::assertSame($inner1->y, $inner2->y);
        static::assertSame($inner1->width, $inner2->width);
        static::assertSame($inner1->height, $inner2->height);
    }

    public function testInnerAreaWithAllPadding(): void
    {
        $area = new Rect(0, 0, 20, 10);

        $block = Block::new()
            ->border(Border::rounded())
            ->margin(top: 1, right: 1, bottom: 1, left: 1)
            ->padding(top: 1, right: 1, bottom: 1, left: 1);

        $inner = $block->innerArea($area);

        static::assertSame(3, $inner->x);
        static::assertSame(3, $inner->y);
        static::assertSame(14, $inner->width);
        static::assertSame(4, $inner->height);
    }

    public function testBackgroundWithNoBorder(): void
    {
        $buffer = new Buffer(5, 3);
        $area = new Rect(0, 0, 5, 3);

        $bg = Color\bright_black();

        Block::new()
            ->border(null)
            ->background($bg)
            ->render($area, Paragraph::new([Line::new([Span::raw('Hi')])]), $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->background);
    }

    public function testBackgroundExactArea(): void
    {
        $buffer = new Buffer(6, 4);
        $area = new Rect(0, 0, 6, 4);

        $bg = Color\blue();

        Block::new()
            ->border(Border::rounded())
            ->background($bg)
            ->render($area, Paragraph::new([]), $buffer);

        static::assertNull($buffer->get(1, 0)?->background);
        static::assertNull($buffer->get(0, 1)?->background);
        static::assertNull($buffer->get(5, 1)?->background);
        static::assertNull($buffer->get(1, 3)?->background);

        static::assertNotNull($buffer->get(1, 1)?->background);
        static::assertNotNull($buffer->get(4, 1)?->background);
        static::assertNotNull($buffer->get(1, 2)?->background);
        static::assertNotNull($buffer->get(4, 2)?->background);
    }

    public function testBackgroundWithTopBorderDisabled(): void
    {
        $buffer = new Buffer(6, 4);
        $area = new Rect(0, 0, 6, 4);

        $bg = Color\blue();

        Block::new()
            ->border(new Border(BorderStyle::Rounded, top: false, right: true, bottom: true, left: true))
            ->background($bg)
            ->render($area, Paragraph::new([]), $buffer);

        static::assertNotNull($buffer->get(1, 0)?->background);
    }

    public function testBorderTopEdge(): void
    {
        $buffer = new Buffer(5, 3);
        $area = new Rect(0, 0, 5, 3);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{256D}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(2, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(3, 0)?->grapheme);
        static::assertSame("\u{256E}", $buffer->get(4, 0)?->grapheme);
    }

    public function testBorderBottomEdge(): void
    {
        $buffer = new Buffer(5, 3);
        $area = new Rect(0, 0, 5, 3);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{2570}", $buffer->get(0, 2)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(1, 2)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(2, 2)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(3, 2)?->grapheme);
        static::assertSame("\u{256F}", $buffer->get(4, 2)?->grapheme);
    }

    public function testBorderSideEdges(): void
    {
        $buffer = new Buffer(5, 5);
        $area = new Rect(0, 0, 5, 5);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{2502}", $buffer->get(0, 1)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 2)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 3)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(4, 1)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(4, 2)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(4, 3)?->grapheme);
        static::assertSame(' ', $buffer->get(1, 1)?->grapheme);
    }

    public function testTitleTruncation(): void
    {
        $buffer = new Buffer(6, 3);
        $area = new Rect(0, 0, 6, 3);

        Block::new()
            ->title('ABCDEFGH')
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        static::assertSame('A', $buffer->get(1, 0)?->grapheme);
        static::assertSame('B', $buffer->get(2, 0)?->grapheme);
        static::assertSame('C', $buffer->get(3, 0)?->grapheme);
        static::assertSame('D', $buffer->get(4, 0)?->grapheme);
        static::assertSame("\u{256E}", $buffer->get(5, 0)?->grapheme);
    }

    public function testTitleWithWideCharacters(): void
    {
        $buffer = new Buffer(8, 3);
        $area = new Rect(0, 0, 8, 3);

        Block::new()
            ->title("\u{6F22}\u{5B57}")
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{6F22}", $buffer->get(1, 0)?->grapheme);
        static::assertSame('', $buffer->get(2, 0)?->grapheme);
        static::assertSame("\u{5B57}", $buffer->get(3, 0)?->grapheme);
        static::assertSame('', $buffer->get(4, 0)?->grapheme);
    }

    public function testTitleStyleModifierIsApplied(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        Block::new()
            ->title('Test')
            ->titleStyle(style: Style\bold())
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testCornerRendersHorizontalWhenOnlyTopBorder(): void
    {
        $buffer = new Buffer(5, 3);
        $area = new Rect(0, 0, 5, 3);

        Block::new()->border(
            new Border(BorderStyle::Rounded, top: true, right: false, bottom: false, left: false),
        )->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{2500}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(4, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(0, 1)?->grapheme);
    }

    public function testWidth1Border(): void
    {
        $buffer = new Buffer(1, 3);
        $area = new Rect(0, 0, 1, 3);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{256D}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(0, 1)?->grapheme);
        static::assertSame("\u{2570}", $buffer->get(0, 2)?->grapheme);
    }

    public function testHeight1Border(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        Block::new()->border(Border::rounded())->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{256D}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{2500}", $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{256E}", $buffer->get(4, 0)?->grapheme);
    }

    public function testTitleNarrowWidth(): void
    {
        $buffer = new Buffer(2, 3);
        $area = new Rect(0, 0, 2, 3);

        Block::new()
            ->title('Test')
            ->border(Border::rounded())
            ->render($area, Paragraph::new([]), $buffer);

        static::assertSame("\u{256D}", $buffer->get(0, 0)?->grapheme);
        static::assertSame("\u{256E}", $buffer->get(1, 0)?->grapheme);
    }
}
