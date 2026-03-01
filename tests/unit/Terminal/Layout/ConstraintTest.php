<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Layout;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Layout\Constraint;
use Psl\Terminal\Layout\ConstraintKind;

final class ConstraintTest extends TestCase
{
    public function testFill(): void
    {
        $c = Constraint::fill();

        static::assertSame(ConstraintKind::Fill, $c->kind);
        static::assertTrue($c->isFill());
        static::assertFalse($c->isFixed());
        static::assertFalse($c->isMin());
        static::assertFalse($c->isMax());
    }

    public function testFixed(): void
    {
        $c = Constraint::fixed(10);

        static::assertSame(ConstraintKind::Fixed, $c->kind);
        static::assertTrue($c->isFixed());
        static::assertFalse($c->isFill());
        static::assertSame(10, $c->size);
        static::assertNull($c->inner);
    }

    public function testMin(): void
    {
        $inner = Constraint::fixed(5);
        $c = Constraint::min(3, $inner);

        static::assertSame(ConstraintKind::Min, $c->kind);
        static::assertTrue($c->isMin());
        static::assertSame(3, $c->size);
        static::assertSame($inner, $c->inner);
    }

    public function testMax(): void
    {
        $inner = Constraint::fixed(10);
        $c = Constraint::max(8, $inner);

        static::assertSame(ConstraintKind::Max, $c->kind);
        static::assertTrue($c->isMax());
        static::assertSame(8, $c->size);
        static::assertSame($inner, $c->inner);
    }
}
