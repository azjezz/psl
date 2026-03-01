<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal\Widget\Span;

final class SpanTest extends TestCase
{
    public function testRaw(): void
    {
        $span = Span::raw('hello');

        static::assertSame('hello', $span->content);
        static::assertNull($span->foreground);
        static::assertNull($span->background);
        static::assertSame([], $span->modifiers);
    }

    public function testStyled(): void
    {
        $span = Span::styled('hello', foreground: Color\red(), style: Style\bold());

        static::assertSame('hello', $span->content);
        static::assertNotNull($span->foreground);
        static::assertNull($span->background);
        static::assertCount(1, $span->modifiers);
    }

    public function testStyledWithBackground(): void
    {
        $span = Span::styled('test', foreground: Color\red(), background: Color\blue());

        static::assertNotNull($span->foreground);
        static::assertNotNull($span->background);
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
        $fg = Color\red();
        $bg = Color\blue();
        $bold = Style\bold();
        $span = Span::styled('hello world', foreground: $fg, background: $bg, style: $bold);

        $sliced = $span->withContent('hello');

        static::assertSame('hello', $sliced->content);
        static::assertSame($fg, $sliced->foreground);
        static::assertSame($bg, $sliced->background);
        static::assertSame($span->modifiers, $sliced->modifiers);
    }

    public function testWithContentOnRawSpan(): void
    {
        $span = Span::raw('hello world');

        $sliced = $span->withContent('hello');

        static::assertSame('hello', $sliced->content);
        static::assertNull($sliced->foreground);
        static::assertNull($sliced->background);
        static::assertSame([], $sliced->modifiers);
    }

    public function testStyledWithMultipleModifiers(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();
        $span = Span::styled('hello', foreground: Color\red(), modifiers: [$bold, $italic]);

        static::assertSame('hello', $span->content);
        static::assertCount(2, $span->modifiers);
        static::assertSame($bold, $span->modifiers[0]);
        static::assertSame($italic, $span->modifiers[1]);
    }

    public function testStyledWithStyleAndModifiersMerged(): void
    {
        $bold = Style\bold();
        $italic = Style\italic();
        $span = Span::styled('hello', style: $bold, modifiers: [$italic]);

        static::assertCount(2, $span->modifiers);
        static::assertSame($italic, $span->modifiers[0]);
        static::assertSame($bold, $span->modifiers[1]);
    }
}
