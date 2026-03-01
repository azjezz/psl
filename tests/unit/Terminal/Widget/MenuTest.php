<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Menu;
use Psl\Terminal\Widget\MenuItem;
use Psl\Terminal\Widget\Span;

final class MenuTest extends TestCase
{
    public function testRenderItems(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $list = Menu::new([
            MenuItem::raw('Item 1'),
            MenuItem::raw('Item 2'),
            MenuItem::raw('Item 3'),
        ]);

        $list->render($area, $buffer);

        static::assertSame('I', $buffer->get(0, 0)?->grapheme);
        static::assertSame('I', $buffer->get(0, 1)?->grapheme);
        static::assertSame('I', $buffer->get(0, 2)?->grapheme);
    }

    public function testHighlight(): void
    {
        $buffer = new Buffer(20, 5);
        $area = new Rect(0, 0, 20, 5);

        $fg = Color\bright_white();

        $list = Menu::new([
            MenuItem::raw('Item 1'),
            MenuItem::raw('Item 2'),
        ])->highlight(1)->highlightStyle(foreground: $fg, style: Style\bold());

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 1);
        static::assertNotNull($cell);
        static::assertSame('I', $cell->grapheme);
        static::assertNotNull($cell->foreground);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('I', $cell->grapheme);
        static::assertNull($cell->foreground);
    }

    public function testClipsToArea(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $list = Menu::new([
            MenuItem::raw('Item 1'),
            MenuItem::raw('Item 2'),
            MenuItem::raw('Item 3'),
            MenuItem::raw('Item 4'),
        ]);

        $list->render($area, $buffer);

        static::assertSame('I', $buffer->get(0, 0)?->grapheme);
        static::assertSame('I', $buffer->get(0, 1)?->grapheme);
    }

    public function testScrollSkipsItems(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $list = Menu::new([
            MenuItem::raw('Alpha'),
            MenuItem::raw('Bravo'),
            MenuItem::raw('Charlie'),
            MenuItem::raw('Delta'),
        ])->scroll(1);

        $list->render($area, $buffer);

        static::assertSame('B', $buffer->get(0, 0)?->grapheme);
        static::assertSame('C', $buffer->get(0, 1)?->grapheme);
    }

    public function testScrollClampsToMax(): void
    {
        $buffer = new Buffer(20, 3);
        $area = new Rect(0, 0, 20, 3);

        $list = Menu::new([
            MenuItem::raw('Alpha'),
            MenuItem::raw('Bravo'),
            MenuItem::raw('Charlie'),
        ])->scroll(100);

        $list->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(0, 1)?->grapheme);
        static::assertSame('C', $buffer->get(0, 2)?->grapheme);
    }

    public function testScrollWithHighlight(): void
    {
        $buffer = new Buffer(20, 2);
        $area = new Rect(0, 0, 20, 2);

        $fg = Color\bright_white();

        $list = Menu::new([
            MenuItem::raw('Alpha'),
            MenuItem::raw('Bravo'),
            MenuItem::raw('Charlie'),
        ])
            ->scroll(1)
            ->highlight(2)
            ->highlightStyle(foreground: $fg);

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 1);
        static::assertNotNull($cell);
        static::assertSame('C', $cell->grapheme);
        static::assertNotNull($cell->foreground);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('B', $cell->grapheme);
        static::assertNull($cell->foreground);
    }

    public function testStyledMenuItem(): void
    {
        $buffer = new Buffer(20, 3);
        $area = new Rect(0, 0, 20, 3);

        $red = Color\red();

        $list = Menu::new([
            MenuItem::styled([
                Span::styled('Quit', foreground: $red),
            ]),
        ]);

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('Q', $cell->grapheme);
        static::assertSame($red, $cell->foreground);
    }

    public function testEmptyAreaDoesNotRender(): void
    {
        $buffer = new Buffer(10, 5);
        $area = new Rect(0, 0, 0, 0);

        $list = Menu::new([
            MenuItem::raw('Item 1'),
        ]);

        $list->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testTextClipsToAreaWidth(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 3, 1);

        $list = Menu::new([
            MenuItem::raw('ABCDEF'),
        ]);

        $list->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(1, 0)?->grapheme);
        static::assertSame('C', $buffer->get(2, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(3, 0)?->grapheme);
    }

    public function testWideCharacterInMenuItem(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $list = Menu::new([
            MenuItem::raw('漢字'),
        ]);

        $list->render($area, $buffer);

        static::assertSame('漢', $buffer->get(0, 0)?->grapheme);
        static::assertSame('', $buffer->get(1, 0)?->grapheme);
        static::assertSame('字', $buffer->get(2, 0)?->grapheme);
        static::assertSame('', $buffer->get(3, 0)?->grapheme);
    }

    public function testHighlightBackgroundFillsRow(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $bg = Color\blue();

        $list = Menu::new([
            MenuItem::raw('Hi'),
        ])->highlight(0)->highlightStyle(background: $bg);

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('H', $cell->grapheme);
        static::assertSame($bg, $cell->background);

        $cell = $buffer->get(5, 0);
        static::assertNotNull($cell);
        static::assertSame(' ', $cell->grapheme);
        static::assertSame($bg, $cell->background);

        $cell = $buffer->get(9, 0);
        static::assertNotNull($cell);
        static::assertSame(' ', $cell->grapheme);
        static::assertSame($bg, $cell->background);
    }

    public function testHighlightOverridesForeground(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $red = Color\red();
        $green = Color\green();

        $list = Menu::new([
            MenuItem::styled([Span::styled('Hi', foreground: $red)]),
        ])->highlight(0)->highlightStyle(foreground: $green);

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame($green, $cell->foreground);
    }

    public function testHighlightOverridesBackground(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $red = Color\red();
        $blue = Color\blue();

        $list = Menu::new([
            MenuItem::styled([Span::styled('Hi', background: $red)]),
        ])->highlight(0)->highlightStyle(background: $blue);

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame($blue, $cell->background);
    }

    public function testHighlightOverridesModifiers(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $list = Menu::new([
            MenuItem::styled([Span::styled('Hi', modifiers: [Style\italic()])]),
        ])->highlight(0)->highlightStyle(style: Style\bold());

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testHighlightStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $list = Menu::new([
            MenuItem::raw('Item'),
        ])->highlight(0)->highlightStyle(style: Style\bold());

        $list->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testMenuScrollClampsToZero(): void
    {
        $buffer = new Buffer(20, 3);
        $area = new Rect(0, 0, 20, 3);

        $list = Menu::new([
            MenuItem::raw('Alpha'),
            MenuItem::raw('Bravo'),
            MenuItem::raw('Charlie'),
        ])->scroll(0);

        $list->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
    }

    public function testMenuClipsMultiSpanAtAreaRight(): void
    {
        $buffer = new Buffer(10, 2);
        $area = new Rect(0, 0, 3, 2);

        $list = Menu::new([
            MenuItem::styled([Span::raw('AB'), Span::raw('CD')]),
            MenuItem::raw('Second'),
        ]);

        $list->render($area, $buffer);

        static::assertSame('A', $buffer->get(0, 0)?->grapheme);
        static::assertSame('B', $buffer->get(1, 0)?->grapheme);
        static::assertSame('C', $buffer->get(2, 0)?->grapheme);
        static::assertSame('S', $buffer->get(0, 1)?->grapheme);
    }

    public function testMenuHighlightFillDoesNotExceedRight(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        $bg = Color\blue();

        $list = Menu::new([
            MenuItem::raw('Hi'),
        ])->highlight(0)->highlightStyle(background: $bg);

        $list->render($area, $buffer);

        static::assertSame($bg, $buffer->get(4, 0)?->background);
        static::assertNull($buffer->get(5, 0));
    }

    public function testHighlightWithoutBackgroundDoesNotFillRow(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $list = Menu::new([
            MenuItem::raw('Hi'),
        ])->highlight(0)->highlightStyle(foreground: Color\red());

        $list->render($area, $buffer);

        $cell = $buffer->get(5, 0);
        static::assertNotNull($cell);
        static::assertSame(' ', $cell->grapheme);
        static::assertNull($cell->background);
    }
}
