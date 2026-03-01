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
}
