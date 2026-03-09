<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;
use Psl\Terminal\Widget\TextInput;

final class TextInputTest extends TestCase
{
    public function testRenderValue(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('hello')
            ->cursor(5)
            ->render($area, $buffer);

        static::assertSame('h', $buffer->get(0, 0)?->grapheme);
        static::assertSame('e', $buffer->get(1, 0)?->grapheme);
        static::assertSame('l', $buffer->get(2, 0)?->grapheme);
        static::assertSame('l', $buffer->get(3, 0)?->grapheme);
        static::assertSame('o', $buffer->get(4, 0)?->grapheme);

        static::assertSame("\u{2588}", $buffer->get(5, 0)?->grapheme);
    }

    public function testRenderPlaceholder(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $placeholderFg = Ansi\foreground(Color\bright_black());

        TextInput::new()
            ->placeholder('Type here...')
            ->placeholderStyle($placeholderFg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertSame('T', $cell->grapheme);
        static::assertSame('y', $buffer->get(1, 0)?->grapheme);
    }

    public function testCursorInMiddleOfText(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $cursorFg = Ansi\foreground(Color\bright_green());

        TextInput::new()
            ->value('abcde')
            ->cursor(2)
            ->cursorStyle($cursorFg)
            ->render($area, $buffer);

        $cell = $buffer->get(2, 0);
        static::assertNotNull($cell);
        static::assertSame('c', $cell->grapheme);
        static::assertNotEmpty($cell->style);
        static::assertSame([], $buffer->get(0, 0)?->style);
    }

    public function testScrollWhenTextExceedsWidth(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        TextInput::new()
            ->value('abcdefghij')
            ->cursor(8)
            ->render($area, $buffer);

        static::assertSame('e', $buffer->get(0, 0)?->grapheme);
        static::assertSame('f', $buffer->get(1, 0)?->grapheme);
        static::assertSame('g', $buffer->get(2, 0)?->grapheme);
        static::assertSame('h', $buffer->get(3, 0)?->grapheme);
        static::assertSame('i', $buffer->get(4, 0)?->grapheme);
    }

    public function testEmptyAreaRendersNothing(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 0, 0);

        TextInput::new()->value('test')->render($area, $buffer);

        static::assertSame(' ', $buffer->get(0, 0)?->grapheme);
    }

    public function testStyleApplied(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $fg = Ansi\foreground(Color\bright_white());

        TextInput::new()
            ->value('hello')
            ->cursor(0)
            ->style($fg)
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->cursor(0)
            ->style(Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testCursorStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->cursor(0)
            ->cursorStyle(Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testPlaceholderStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->placeholder('Type...')
            ->placeholderStyle(Style\italic())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testCursorClamp(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->cursor(0)
            ->render($area, $buffer);

        static::assertSame('H', $buffer->get(0, 0)?->grapheme);
    }

    public function testEmptyAreaDoesNotCorruptBuffer(): void
    {
        $buffer = new Buffer(10, 1);
        $buffer->set(0, 0, new Cell('X'));
        $area = new Rect(0, 0, 0, 0);

        TextInput::new()->value('Test')->render($area, $buffer);

        static::assertSame('X', $buffer->get(0, 0)?->grapheme);
    }

    public function testPlaceholderCursorRendered(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $fg = Ansi\foreground(Color\bright_white());

        TextInput::new()
            ->placeholder('Type here')
            ->cursorStyle($fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
    }

    public function testCursorScrollsWithLongInput(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        TextInput::new()
            ->value('ABCDEFGHIJ')
            ->cursor(7)
            ->render($area, $buffer);

        static::assertSame('D', $buffer->get(0, 0)?->grapheme);
    }

    public function testCursorPositionInOutput(): void
    {
        $buffer = new Buffer(10, 1);
        $area = new Rect(0, 0, 10, 1);

        $fg = Ansi\foreground(Color\red());

        TextInput::new()
            ->value('Hello')
            ->cursor(2)
            ->cursorStyle($fg)
            ->render($area, $buffer);

        $cell = $buffer->get(2, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->style);
        static::assertSame('l', $cell->grapheme);
    }

    public function testPlaceholderMode(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->placeholder('Type here')
            ->render($area, $buffer);

        static::assertSame('H', $buffer->get(0, 0)?->grapheme);
    }

    public function testWideCursorPosition(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $fg = Ansi\foreground(Color\red());

        TextInput::new()
            ->value("\u{4e16}\u{754c}Hi")
            ->cursor(2)
            ->cursorStyle($fg)
            ->render($area, $buffer);

        static::assertSame("\u{4e16}", $buffer->get(0, 0)?->grapheme);
        static::assertSame('', $buffer->get(1, 0)?->grapheme);
        static::assertSame("\u{754c}", $buffer->get(2, 0)?->grapheme);
        static::assertSame('', $buffer->get(3, 0)?->grapheme);

        $cursorCell = $buffer->get(4, 0);
        static::assertNotNull($cursorCell);
        static::assertSame('H', $cursorCell->grapheme);
        static::assertNotEmpty($cursorCell->style);
    }

    public function testWideCharScrollOffset(): void
    {
        $buffer = new Buffer(5, 1);
        $area = new Rect(0, 0, 5, 1);

        TextInput::new()
            ->value("\u{4e16}\u{754c}\u{4f60}\u{597d}")
            ->cursor(3)
            ->render($area, $buffer);

        $cursorCell = $buffer->get(4, 0);
        static::assertNotNull($cursorCell);
        static::assertSame("\u{597d}", $cursorCell->grapheme);
    }
}
