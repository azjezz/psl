<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Buffer;
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

        $placeholderFg = Color\bright_black();

        TextInput::new()
            ->placeholder('Type here...')
            ->placeholderStyle(foreground: $placeholderFg)
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

        $cursorFg = Color\bright_green();

        TextInput::new()
            ->value('abcde')
            ->cursor(2)
            ->cursorStyle(foreground: $cursorFg)
            ->render($area, $buffer);

        $cell = $buffer->get(2, 0);
        static::assertNotNull($cell);
        static::assertSame('c', $cell->grapheme);
        static::assertNotNull($cell->foreground);
        static::assertNull($buffer->get(0, 0)?->foreground);
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

        $fg = Color\bright_white();

        TextInput::new()
            ->value('hello')
            ->cursor(0)
            ->style(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
    }

    public function testStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->cursor(0)
            ->style(style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testCursorStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->value('Hello')
            ->cursor(0)
            ->cursorStyle(style: Style\bold())
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
    }

    public function testPlaceholderStyleModifier(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        TextInput::new()
            ->placeholder('Type...')
            ->placeholderStyle(style: Style\italic())
            ->render($area, $buffer);

        $cell = $buffer->get(1, 0);
        static::assertNotNull($cell);
        static::assertNotEmpty($cell->modifiers);
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
        $buffer->set(0, 0, new \Psl\Terminal\Cell('X'));
        $area = new Rect(0, 0, 0, 0);

        TextInput::new()->value('Test')->render($area, $buffer);

        static::assertSame('X', $buffer->get(0, 0)?->grapheme);
    }

    public function testPlaceholderCursorRendered(): void
    {
        $buffer = new Buffer(20, 1);
        $area = new Rect(0, 0, 20, 1);

        $fg = Color\bright_white();

        TextInput::new()
            ->placeholder('Type here')
            ->cursorStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(0, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
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

        $fg = Color\red();

        TextInput::new()
            ->value('Hello')
            ->cursor(2)
            ->cursorStyle(foreground: $fg)
            ->render($area, $buffer);

        $cell = $buffer->get(2, 0);
        static::assertNotNull($cell);
        static::assertNotNull($cell->foreground);
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
}
