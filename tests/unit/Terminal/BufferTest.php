<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;

final class BufferTest extends TestCase
{
    public function testConstruction(): void
    {
        $buffer = new Buffer(10, 5);

        static::assertSame(10, $buffer->getWidth());
        static::assertSame(5, $buffer->getHeight());
    }

    public function testGetDefault(): void
    {
        $buffer = new Buffer(10, 5);
        $cell = $buffer->get(0, 0);

        static::assertNotNull($cell);
        static::assertSame(' ', $cell->grapheme);
        static::assertNull($cell->foreground);
    }

    public function testGetOutOfBoundsReturnsNull(): void
    {
        $buffer = new Buffer(10, 5);

        static::assertNull($buffer->get(-1, 0));
        static::assertNull($buffer->get(0, -1));
        static::assertNull($buffer->get(10, 0));
        static::assertNull($buffer->get(0, 5));
    }

    public function testSetAndGet(): void
    {
        $buffer = new Buffer(10, 5);
        $cell = new Cell('X', Color\red());

        $buffer->set(3, 2, $cell);
        $result = $buffer->get(3, 2);

        static::assertNotNull($result);
        static::assertSame('X', $result->grapheme);
        static::assertSame($cell->foreground, $result->foreground);
    }

    public function testSetOutOfBounds(): void
    {
        $buffer = new Buffer(10, 5);
        $cell = new Cell('X');

        $buffer->set(-1, 0, $cell);
        $buffer->set(10, 0, $cell);
        $buffer->set(0, -1, $cell);
        $buffer->set(0, 5, $cell);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testSetString(): void
    {
        $buffer = new Buffer(10, 5);
        $buffer->setString(2, 1, 'Hello', Color\green());

        static::assertSame('H', $buffer->get(2, 1)?->grapheme);
        static::assertSame('e', $buffer->get(3, 1)?->grapheme);
        static::assertSame('l', $buffer->get(4, 1)?->grapheme);
        static::assertSame('l', $buffer->get(5, 1)?->grapheme);
        static::assertSame('o', $buffer->get(6, 1)?->grapheme);
        static::assertSame(' ', $buffer->get(7, 1)?->grapheme);
    }

    public function testFill(): void
    {
        $buffer = new Buffer(3, 2);
        $cell = new Cell('#');
        $buffer->fill($cell);

        for ($y = 0; $y < 2; $y++) {
            for ($x = 0; $x < 3; $x++) {
                static::assertSame('#', $buffer->get($x, $y)?->grapheme);
            }
        }
    }

    public function testClear(): void
    {
        $buffer = new Buffer(3, 2);
        $buffer->set(0, 0, new Cell('X'));
        $buffer->clear();

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testResize(): void
    {
        $buffer = new Buffer(5, 5);
        $buffer->set(0, 0, new Cell('X'));

        $buffer->resize(10, 8);

        static::assertSame(10, $buffer->getWidth());
        static::assertSame(8, $buffer->getHeight());
        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testSetStringOutOfBoundsY(): void
    {
        $buffer = new Buffer(10, 5);

        $buffer->setString(0, -1, 'Hello');
        $buffer->setString(0, 5, 'Hello');

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testSetStringWideCharacter(): void
    {
        $buffer = new Buffer(10, 1);
        $buffer->setString(0, 0, '漢字');

        static::assertSame('漢', $buffer->get(0, 0)?->grapheme);
        static::assertSame('', $buffer->get(1, 0)?->grapheme);
        static::assertSame('字', $buffer->get(2, 0)?->grapheme);
        static::assertSame('', $buffer->get(3, 0)?->grapheme);
    }

    public function testFlushWritesAllCellsOnFirstCall(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(0, 0, 'Hi');

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $written = $output->getBuffer();

        static::assertStringContainsString('H', $written);
        static::assertStringContainsString('i', $written);
    }

    public function testFlushSkipsUnchangedCells(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(0, 0, 'AB');

        $output = new IO\MemoryHandle();

        $buffer->flush($output);
        $firstLen = Str\Byte\length($output->getBuffer());

        $buffer->set(0, 0, new Cell('X'));
        $buffer->flush($output);

        $secondWrite = Str\Byte\slice($output->getBuffer(), $firstLen);

        static::assertStringContainsString('X', $secondWrite);
        static::assertStringNotContainsString('B', $secondWrite);
    }

    public function testFlushWritesNothingWhenUnchanged(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(0, 0, 'AB');

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $afterFirst = Str\Byte\length($output->getBuffer());

        $buffer->flush($output);

        static::assertSame($afterFirst, Str\Byte\length($output->getBuffer()));
    }

    public function testFlushWithForegroundAndBackground(): void
    {
        $buffer = new Buffer(1, 1);
        $fg = Color\red();
        $bg = Color\blue();
        $buffer->set(0, 0, new Cell('X', $fg, $bg));

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $written = $output->getBuffer();

        static::assertStringContainsString('X', $written);
        static::assertStringContainsString("\e[", $written);
    }

    public function testFlushWithModifiers(): void
    {
        $buffer = new Buffer(1, 1);
        $bold = Style\bold();
        $buffer->set(0, 0, new Cell('B', modifiers: [$bold]));

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $written = $output->getBuffer();

        static::assertStringContainsString('B', $written);
        static::assertStringContainsString("\e[", $written);
    }

    public function testResizeClearsPreviousForFullRedraw(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(0, 0, 'AB');

        $output = new IO\MemoryHandle();
        $buffer->flush($output);
        $buffer->resize(3, 1);
        $buffer->setString(0, 0, 'AB');

        $afterResize = Str\Byte\length($output->getBuffer());
        $buffer->flush($output);

        $redrawWrite = Str\Byte\slice($output->getBuffer(), $afterResize);

        static::assertStringContainsString('A', $redrawWrite);
        static::assertStringContainsString('B', $redrawWrite);
    }

    public function testSetAtExactBoundary(): void
    {
        $buffer = new Buffer(5, 3);
        $cell = new Cell('Z');

        $buffer->set(4, 2, $cell);
        static::assertSame('Z', $buffer->get(4, 2)?->grapheme);

        $buffer->set(5, 0, $cell);
        static::assertNull($buffer->get(5, 0));

        $buffer->set(0, 3, $cell);
        static::assertNull($buffer->get(0, 3));
    }

    public function testSetStringAtYBoundary(): void
    {
        $buffer = new Buffer(10, 3);

        $buffer->setString(0, 2, 'Hi');
        static::assertSame('H', $buffer->get(0, 2)?->grapheme);

        $buffer->setString(0, 3, 'Bad');
        static::assertNull($buffer->get(0, 3));
    }

    public function testSetStringStopsAtWidthBoundary(): void
    {
        $buffer = new Buffer(5, 1);
        $buffer->setString(3, 0, 'ABC');

        static::assertSame('A', $buffer->get(3, 0)?->grapheme);
        static::assertSame('B', $buffer->get(4, 0)?->grapheme);
        static::assertNull($buffer->get(5, 0));
    }

    public function testSetStringWideCharAtBoundary(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(2, 0, '漢');

        static::assertSame('漢', $buffer->get(2, 0)?->grapheme);
        static::assertNull($buffer->get(3, 0));
    }

    public function testFillDoesNotExceedBounds(): void
    {
        $buffer = new Buffer(2, 2);
        $buffer->fill(new Cell('#'));

        static::assertSame('#', $buffer->get(0, 0)?->grapheme);
        static::assertSame('#', $buffer->get(1, 0)?->grapheme);
        static::assertSame('#', $buffer->get(0, 1)?->grapheme);
        static::assertSame('#', $buffer->get(1, 1)?->grapheme);
        static::assertNull($buffer->get(2, 0));
        static::assertNull($buffer->get(0, 2));
    }

    public function testFlushContinuesAfterUnchangedCell(): void
    {
        $buffer = new Buffer(3, 1);
        $buffer->setString(0, 0, 'ABC');

        $output = new IO\MemoryHandle();
        $buffer->flush($output);
        $firstLen = Str\Byte\length($output->getBuffer());

        $buffer->set(2, 0, new Cell('Z'));
        $buffer->flush($output);

        $secondWrite = Str\Byte\slice($output->getBuffer(), $firstLen);
        static::assertStringContainsString('Z', $secondWrite);
    }

    public function testFlushCursorPositions(): void
    {
        $buffer = new Buffer(3, 2);
        $buffer->set(0, 0, new Cell('A'));
        $buffer->set(2, 1, new Cell('B'));

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $written = $output->getBuffer();
        static::assertStringContainsString("\e[1;1H", $written);
        static::assertStringContainsString("\e[2;3H", $written);
    }

    public function testFlushAppliesModifiersWithoutColors(): void
    {
        $buffer = new Buffer(1, 1);
        $bold = Style\bold();
        $buffer->set(0, 0, new Cell('X', null, null, [$bold]));

        $output = new IO\MemoryHandle();
        $buffer->flush($output);

        $written = $output->getBuffer();
        static::assertStringContainsString("\e[0m", $written);
    }

    public function testFlushWritesNothingWhenUnchangedMultiRow(): void
    {
        $buffer = new Buffer(2, 3);
        $buffer->setString(0, 0, 'AB');
        $buffer->setString(0, 1, 'CD');
        $buffer->setString(0, 2, 'EF');

        $output = new IO\MemoryHandle();
        $buffer->flush($output);
        $afterFirst = Str\Byte\length($output->getBuffer());

        $buffer->flush($output);
        static::assertSame($afterFirst, Str\Byte\length($output->getBuffer()));
    }
}
