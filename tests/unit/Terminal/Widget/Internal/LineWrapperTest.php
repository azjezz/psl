<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Widget\Internal\LineWrapper;
use Psl\Terminal\Widget\Line;
use Psl\Terminal\Widget\Span;
use Psl\Terminal\Widget\Wrap;

final class LineWrapperTest extends TestCase
{
    public function testNoWrapReturnsOriginal(): void
    {
        $lines = [Line::new([Span::raw('Hello World')])];

        $result = LineWrapper::wrap($lines, Wrap::None, 5);

        static::assertCount(1, $result);
        static::assertSame('Hello World', $result[0]->spans[0]->content);
    }

    public function testShortLineNotWrapped(): void
    {
        $lines = [Line::new([Span::raw('Hi')])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 10);

        static::assertCount(1, $result);
        static::assertSame('Hi', $result[0]->spans[0]->content);
    }

    public function testWordWrapBasic(): void
    {
        $lines = [Line::new([Span::raw('Hello World Test')])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 11);

        static::assertCount(2, $result);
        static::assertSame('Hello World', $result[0]->spans[0]->content);
        static::assertSame('Test', $result[1]->spans[0]->content);
    }

    public function testCharWrapBasic(): void
    {
        $lines = [Line::new([Span::raw('ABCDEFGHIJ')])];

        $result = LineWrapper::wrap($lines, Wrap::Char, 5);

        static::assertCount(2, $result);
        static::assertSame('ABCDE', $result[0]->spans[0]->content);
        static::assertSame('FGHIJ', $result[1]->spans[0]->content);
    }

    /**
     * Bug fix: Str\search finds first occurrence, so repeated words got styles from wrong position.
     */
    public function testWordWrapRepeatedTextPreservesCorrectStyles(): void
    {
        $red = Color\red();
        $blue = Color\blue();

        $lines = [Line::new([
            Span::styled('hello ', foreground: $red),
            Span::styled('world ', foreground: $blue),
            Span::styled('hello', foreground: $red),
        ])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 6);

        static::assertCount(3, $result);

        static::assertSame('hello', $result[0]->spans[0]->content);
        static::assertSame($red, $result[0]->spans[0]->foreground);

        static::assertSame('world', $result[1]->spans[0]->content);
        static::assertSame($blue, $result[1]->spans[0]->foreground);

        static::assertSame('hello', $result[2]->spans[0]->content);
        static::assertSame($red, $result[2]->spans[0]->foreground);
    }

    /**
     * Bug fix: modifiers[0] ?? null dropped all modifiers after the first.
     */
    public function testWrapPreservesAllModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();

        $span = Span::styled('Hello World', modifiers: [$bold, $italic]);

        $lines = [Line::new([$span])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 6);

        static::assertCount(2, $result);

        static::assertSame('Hello', $result[0]->spans[0]->content);
        static::assertCount(2, $result[0]->spans[0]->modifiers);
        static::assertSame($bold, $result[0]->spans[0]->modifiers[0]);
        static::assertSame($italic, $result[0]->spans[0]->modifiers[1]);

        static::assertSame('World', $result[1]->spans[0]->content);
        static::assertCount(2, $result[1]->spans[0]->modifiers);
        static::assertSame($bold, $result[1]->spans[0]->modifiers[0]);
        static::assertSame($italic, $result[1]->spans[0]->modifiers[1]);
    }

    public function testWrapPreservesStylesAcrossSpanBoundaries(): void
    {
        $red = Color\red();
        $blue = Color\blue();

        $lines = [Line::new([
            Span::styled('Hello W', foreground: $red),
            Span::styled('orld Test', foreground: $blue),
        ])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 12);

        static::assertCount(2, $result);

        static::assertCount(2, $result[0]->spans);
        static::assertSame('Hello W', $result[0]->spans[0]->content);
        static::assertSame($red, $result[0]->spans[0]->foreground);
        static::assertSame('orld', $result[0]->spans[1]->content);
        static::assertSame($blue, $result[0]->spans[1]->foreground);

        static::assertCount(1, $result[1]->spans);
        static::assertSame('Test', $result[1]->spans[0]->content);
        static::assertSame($blue, $result[1]->spans[0]->foreground);
    }

    public function testCharWrapPreservesStyles(): void
    {
        $red = Color\red();
        $blue = Color\blue();

        $lines = [Line::new([
            Span::styled('ABC', foreground: $red),
            Span::styled('DEF', foreground: $blue),
        ])];

        $result = LineWrapper::wrap($lines, Wrap::Char, 4);

        static::assertCount(2, $result);

        static::assertCount(2, $result[0]->spans);
        static::assertSame('ABC', $result[0]->spans[0]->content);
        static::assertSame($red, $result[0]->spans[0]->foreground);
        static::assertSame('D', $result[0]->spans[1]->content);
        static::assertSame($blue, $result[0]->spans[1]->foreground);

        static::assertCount(1, $result[1]->spans);
        static::assertSame('EF', $result[1]->spans[0]->content);
        static::assertSame($blue, $result[1]->spans[0]->foreground);
    }

    public function testCharWrapRepeatedText(): void
    {
        $red = Color\red();
        $blue = Color\blue();

        $lines = [Line::new([
            Span::styled('AAAA', foreground: $red),
            Span::styled('AAAA', foreground: $blue),
        ])];

        $result = LineWrapper::wrap($lines, Wrap::Char, 4);

        static::assertCount(2, $result);
        static::assertSame('AAAA', $result[0]->spans[0]->content);
        static::assertSame($red, $result[0]->spans[0]->foreground);
        static::assertSame('AAAA', $result[1]->spans[0]->content);
        static::assertSame($blue, $result[1]->spans[0]->foreground);
    }

    public function testZeroWidthReturnsOriginal(): void
    {
        $lines = [Line::new([Span::raw('Hello')])];

        $result = LineWrapper::wrap($lines, Wrap::Word, 0);

        static::assertCount(1, $result);
    }

    public function testEmptyLineNotWrapped(): void
    {
        $lines = [Line::empty()];

        $result = LineWrapper::wrap($lines, Wrap::Word, 10);

        static::assertCount(1, $result);
    }

    public function testMultipleLinesWrappedIndependently(): void
    {
        $lines = [
            Line::new([Span::raw('Hello World')]),
            Line::new([Span::raw('Foo Bar')]),
        ];

        $result = LineWrapper::wrap($lines, Wrap::Word, 6);

        static::assertCount(4, $result);
        static::assertSame('Hello', $result[0]->spans[0]->content);
        static::assertSame('World', $result[1]->spans[0]->content);
        static::assertSame('Foo', $result[2]->spans[0]->content);
        static::assertSame('Bar', $result[3]->spans[0]->content);
    }
}
