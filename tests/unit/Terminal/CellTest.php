<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Cell;

final class CellTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $cell = new Cell();

        static::assertSame(' ', $cell->grapheme);
        static::assertNull($cell->foreground);
        static::assertNull($cell->background);
        static::assertSame([], $cell->modifiers);
    }

    public function testConstructionWithValues(): void
    {
        $fg = Color\red();
        $bg = Color\blue();
        $bold = Style\bold();

        $cell = new Cell('A', $fg, $bg, [$bold]);

        static::assertSame('A', $cell->grapheme);
        static::assertSame($fg, $cell->foreground);
        static::assertSame($bg, $cell->background);
        static::assertSame([$bold], $cell->modifiers);
    }

    public function testEqualsIdentical(): void
    {
        $cell1 = new Cell('X', Color\red(), Color\blue());
        $cell2 = new Cell('X', Color\red(), Color\blue());

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentGrapheme(): void
    {
        $cell1 = new Cell('A');
        $cell2 = new Cell('B');

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDifferentForeground(): void
    {
        $cell1 = new Cell('A', Color\red());
        $cell2 = new Cell('A', Color\blue());

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDefaultCells(): void
    {
        $cell1 = new Cell();
        $cell2 = new Cell();

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentModifierCount(): void
    {
        $bold = Style\bold();

        $cell1 = new Cell('A', modifiers: [$bold]);
        $cell2 = new Cell('A', modifiers: []);

        static::assertFalse($cell1->equals($cell2));
        static::assertFalse($cell2->equals($cell1));
    }

    public function testEqualsMatchingModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();

        $cell1 = new Cell('A', modifiers: [$bold, $italic]);
        $cell2 = new Cell('A', modifiers: [$bold, $italic]);

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();

        $cell1 = new Cell('A', modifiers: [$bold]);
        $cell2 = new Cell('A', modifiers: [$italic]);

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDifferentForegroundNullness(): void
    {
        $cell1 = new Cell('A', Color\red());
        $cell2 = new Cell('A');

        static::assertFalse($cell1->equals($cell2));
        static::assertFalse($cell2->equals($cell1));
    }

    public function testEqualsDifferentBackgroundNullness(): void
    {
        $cell1 = new Cell('A', null, Color\blue());
        $cell2 = new Cell('A');

        static::assertFalse($cell1->equals($cell2));
        static::assertFalse($cell2->equals($cell1));
    }
}
