<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\Tabs;

final class TabsTest extends TestCase
{
    public function testRenderTabs(): void
    {
        $buffer = new Buffer(30, 1);
        $area = new Rect(0, 0, 30, 1);

        Tabs::new()
            ->titles(['Tab1', 'Tab2', 'Tab3'])
            ->highlight(0)
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertSame('T', $buffer->get(1, 0)?->grapheme);
        static::assertSame('a', $buffer->get(2, 0)?->grapheme);
        static::assertSame('b', $buffer->get(3, 0)?->grapheme);
        static::assertSame('1', $buffer->get(4, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(5, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(6, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(7, 0)?->grapheme);
        static::assertSame('T', $buffer->get(8, 0)?->grapheme);
    }

    public function testActiveStyleApplied(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $activeFg = Color\bright_cyan();

        Tabs::new()
            ->titles(['Active', 'Other'])
            ->highlight(0)
            ->activeStyle(foreground: $activeFg, style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testInactiveStyleApplied(): void
    {
        $buffer = new Buffer(30, 1);
        $area = new Rect(0, 0, 30, 1);

        $inactiveFg = Color\bright_black();

        Tabs::new()
            ->titles(['First', 'Second'])
            ->highlight(0)
            ->inactiveStyle(foreground: $inactiveFg)
            ->render($area, $buffer);

        $cell = $buffer->get(9, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testNoHighlightRendersAllInactive(): void
    {
        $buffer = new Buffer(30, 1);
        $area = new Rect(0, 0, 30, 1);

        $activeFg = Color\bright_cyan();
        $inactiveFg = Color\bright_black();

        Tabs::new()
            ->titles(['Tab1', 'Tab2'])
            ->activeStyle(foreground: $activeFg)
            ->inactiveStyle(foreground: $inactiveFg)
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertSame($inactiveFg, $cell->foreground);
    }

    public function testEmptyTitlesRendersNothing(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Tabs::new()->titles([])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 0, 0);

        Tabs::new()->titles(['Tab1'])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testClipsToArea(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        Tabs::new()
            ->titles(['LongTab1', 'LongTab2'])
            ->highlight(0)
            ->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertSame('L', $buffer->get(1, 0)?->grapheme);
    }

    public function testActiveStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Tabs::new()
            ->titles(['Tab1', 'Tab2'])
            ->highlight(0)
            ->activeStyle(style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testInactiveStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Tabs::new()
            ->titles(['Tab1', 'Tab2'])
            ->highlight(0)
            ->inactiveStyle(style: Style\italic())
            ->render($area, $buffer);

        $tCount = 0;
        $tab2Start = -1;
        for ($x = 0; $x < 20; $x++) {
            if ($buffer->get($x, 0)?->grapheme !== 'T') {
                continue;
            }

            $tCount++;
            if ($tCount === 2) {
                $tab2Start = $x;
                break;
            }
        }

        static::assertGreaterThanOrEqual(0, $tab2Start);
        $cell = $buffer->get($tab2Start, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testEmptyAreaDoesNotCorruptBuffer(): void
    {
        $buffer = new Buffer(10, 1);
        $buffer->set(0, 0, new \Psl\Terminal\Cell('X'));
        $area = new Rect(0, 0, 0, 0);

        Tabs::new()->titles(['Tab1'])->render($area, $buffer);

        static::assertSame('X', $buffer->get(0, 0)?->grapheme);
    }

    public function testEmptyTitlesDoesNotRender(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        Tabs::new()->titles([])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testTabsClipToRight(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        Tabs::new()->titles(['ABCDEF', 'GHI'])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertNull($buffer->get(5, 0));
    }

    public function testTabsSeparator(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        Tabs::new()->titles(['AB', 'CD'])->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
        static::assertSame('A', $buffer->get(1, 0)?->grapheme);
        static::assertSame('B', $buffer->get(2, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(3, 0)?->grapheme);
        static::assertSame("\u{2502}", $buffer->get(4, 0)?->grapheme);
        static::assertSame(' ', $buffer->get(5, 0)?->grapheme);
        static::assertSame('C', $buffer->get(6, 0)?->grapheme);
        static::assertSame('D', $buffer->get(7, 0)?->grapheme);
    }
}
