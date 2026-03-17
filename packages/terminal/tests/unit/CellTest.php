<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Cell;

final class CellTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $cell = new Cell();

        static::assertSame(' ', $cell->grapheme);
        static::assertSame([], $cell->style);
    }

    public function testConstructionWithValues(): void
    {
        $fg = Ansi\foreground(Color\red());
        $bg = Ansi\background(Color\blue());
        $bold = Style\bold();

        $cell = new Cell('A', [$fg, $bg, $bold]);

        static::assertSame('A', $cell->grapheme);
        static::assertSame([$fg, $bg, $bold], $cell->style);
    }

    public function testEqualsIdentical(): void
    {
        $cell1 = new Cell('X', [Ansi\foreground(Color\red()), Ansi\background(Color\blue())]);
        $cell2 = new Cell('X', [Ansi\foreground(Color\red()), Ansi\background(Color\blue())]);

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentGrapheme(): void
    {
        $cell1 = new Cell('A');
        $cell2 = new Cell('B');

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDifferentStyle(): void
    {
        $cell1 = new Cell('A', [Ansi\foreground(Color\red())]);
        $cell2 = new Cell('A', [Ansi\foreground(Color\blue())]);

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDefaultCells(): void
    {
        $cell1 = new Cell();
        $cell2 = new Cell();

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentStyleCount(): void
    {
        $bold = Style\bold();

        $cell1 = new Cell('A', [$bold]);
        $cell2 = new Cell('A', []);

        static::assertFalse($cell1->equals($cell2));
        static::assertFalse($cell2->equals($cell1));
    }

    public function testEqualsMatchingStyle(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();

        $cell1 = new Cell('A', [$bold, $italic]);
        $cell2 = new Cell('A', [$bold, $italic]);

        static::assertTrue($cell1->equals($cell2));
    }

    public function testEqualsDifferentModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();

        $cell1 = new Cell('A', [$bold]);
        $cell2 = new Cell('A', [$italic]);

        static::assertFalse($cell1->equals($cell2));
    }

    public function testEqualsDifferentStyleNullness(): void
    {
        $cell1 = new Cell('A', [Ansi\foreground(Color\red())]);
        $cell2 = new Cell('A');

        static::assertFalse($cell1->equals($cell2));
        static::assertFalse($cell2->equals($cell1));
    }

    public function testStyleEqualEmptyArrays(): void
    {
        static::assertTrue(Cell::styleEqual([], []));
    }

    public function testStyleEqualSameCSIs(): void
    {
        $fg = Ansi\foreground(Color\red());
        $bold = Style\bold();

        static::assertTrue(Cell::styleEqual([$fg, $bold], [$fg, $bold]));
    }

    public function testStyleEqualDifferentCSIs(): void
    {
        $fg = Ansi\foreground(Color\red());
        $bg = Ansi\background(Color\blue());

        static::assertFalse(Cell::styleEqual([$fg], [$bg]));
    }

    public function testStyleEqualDifferentLengths(): void
    {
        $fg = Ansi\foreground(Color\red());

        static::assertFalse(Cell::styleEqual([$fg], []));
        static::assertFalse(Cell::styleEqual([], [$fg]));
    }
}
