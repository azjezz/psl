<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Widget\Span;

final class SpanTest extends TestCase
{
    public function testRaw(): void
    {
        $span = Span::raw('hello');

        static::assertSame('hello', $span->content);
        static::assertSame([], $span->style);
    }

    public function testStyled(): void
    {
        $span = Span::styled('hello', Ansi\foreground(Color\red()), Style\bold());

        static::assertSame('hello', $span->content);
        static::assertCount(2, $span->style);
    }

    public function testStyledWithBackground(): void
    {
        $span = Span::styled('test', Ansi\foreground(Color\red()), Ansi\background(Color\blue()));

        static::assertCount(2, $span->style);
    }

    public function testWidth(): void
    {
        static::assertSame(5, Span::raw('hello')->width());
        static::assertSame(0, Span::raw('')->width());
        static::assertSame(3, Span::raw('abc')->width());
    }

    public function testEmptySpan(): void
    {
        $span = Span::raw('');

        static::assertSame('', $span->content);
        static::assertSame(0, $span->width());
    }

    public function testWithContentPreservesAllStyles(): void
    {
        $fg = Ansi\foreground(Color\red());
        $bg = Ansi\background(Color\blue());
        $bold = Style\bold();
        $span = Span::styled('hello world', $fg, $bg, $bold);

        $sliced = $span->withContent('hello');

        static::assertSame('hello', $sliced->content);
        static::assertSame($span->style, $sliced->style);
    }

    public function testWithContentOnRawSpan(): void
    {
        $span = Span::raw('hello world');

        $sliced = $span->withContent('hello');

        static::assertSame('hello', $sliced->content);
        static::assertSame([], $sliced->style);
    }

    public function testStyledWithMultipleModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();
        $fg = Ansi\foreground(Color\red());
        $span = Span::styled('hello', $fg, $bold, $italic);

        static::assertSame('hello', $span->content);
        static::assertCount(3, $span->style);
        static::assertSame($fg, $span->style[0]);
        static::assertSame($bold, $span->style[1]);
        static::assertSame($italic, $span->style[2]);
    }

    public function testStyledWithModifiersOnly(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();
        $span = Span::styled('hello', $bold, $italic);

        static::assertCount(2, $span->style);
        static::assertSame($bold, $span->style[0]);
        static::assertSame($italic, $span->style[1]);
    }
}
