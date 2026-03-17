<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Widget;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Widget\Line;
use Psl\Terminal\Widget\Span;

final class LineTest extends TestCase
{
    public function testNew(): void
    {
        $spans = [Span::raw('hello'), Span::raw(' world')];
        $line = Line::new($spans);

        static::assertCount(2, $line->spans);
    }

    public function testEmpty(): void
    {
        $line = Line::empty();

        static::assertSame([], $line->spans);
        static::assertSame(0, $line->width());
    }

    public function testWidth(): void
    {
        $line = Line::new([
            Span::raw('hello'),
            Span::raw(' '),
            Span::raw('world'),
        ]);

        static::assertSame(11, $line->width());
    }

    public function testWidthSingleSpan(): void
    {
        $line = Line::new([Span::raw('test')]);

        static::assertSame(4, $line->width());
    }
}
