<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Rect;

final class RectTest extends TestCase
{
    public function testConstruction(): void
    {
        $rect = new Rect(5, 10, 80, 24);

        static::assertSame(5, $rect->x);
        static::assertSame(10, $rect->y);
        static::assertSame(80, $rect->width);
        static::assertSame(24, $rect->height);
    }

    public function testFromSize(): void
    {
        $rect = Rect::fromSize(80, 24);

        static::assertSame(0, $rect->x);
        static::assertSame(0, $rect->y);
        static::assertSame(80, $rect->width);
        static::assertSame(24, $rect->height);
    }

    public function testArea(): void
    {
        $rect = new Rect(0, 0, 10, 5);

        static::assertSame(50, $rect->area());
    }

    public function testRight(): void
    {
        $rect = new Rect(5, 0, 10, 5);

        static::assertSame(15, $rect->right());
    }

    public function testBottom(): void
    {
        $rect = new Rect(0, 3, 10, 5);

        static::assertSame(8, $rect->bottom());
    }

    public function testIsEmpty(): void
    {
        static::assertTrue(new Rect(0, 0, 0, 5)->isEmpty());
        static::assertTrue(new Rect(0, 0, 5, 0)->isEmpty());
        static::assertFalse(new Rect(0, 0, 1, 1)->isEmpty());
    }

    public function testInner(): void
    {
        $rect = new Rect(0, 0, 20, 10);
        $inner = $rect->inner(top: 1, right: 1, bottom: 1, left: 1);

        static::assertSame(1, $inner->x);
        static::assertSame(1, $inner->y);
        static::assertSame(18, $inner->width);
        static::assertSame(8, $inner->height);
    }

    public function testInnerClamps(): void
    {
        $rect = new Rect(0, 0, 2, 2);
        $inner = $rect->inner(top: 5, right: 5, bottom: 5, left: 5);

        static::assertSame(0, $inner->width);
        static::assertSame(0, $inner->height);
    }

    public function testContains(): void
    {
        $rect = new Rect(5, 5, 10, 10);

        static::assertTrue($rect->contains(5, 5));
        static::assertTrue($rect->contains(14, 14));
        static::assertFalse($rect->contains(4, 5));
        static::assertFalse($rect->contains(5, 4));
        static::assertFalse($rect->contains(15, 5));
        static::assertFalse($rect->contains(5, 15));
    }

    public function testInnerWithDefaults(): void
    {
        $rect = new Rect(0, 0, 10, 5);
        $inner = $rect->inner();

        static::assertSame(0, $inner->x);
        static::assertSame(0, $inner->y);
        static::assertSame(10, $inner->width);
        static::assertSame(5, $inner->height);
    }
}
