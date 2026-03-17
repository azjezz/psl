<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Layout;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Layout;
use Psl\Terminal\Rect;

final class LayoutTest extends TestCase
{
    public function testVerticalFixedAndFill(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$top, $bottom] = Layout\vertical($rect, [
            Layout\fixed(1),
            Layout\fill(),
        ]);

        static::assertSame(0, $top->y);
        static::assertSame(80, $top->width);
        static::assertSame(1, $top->height);

        static::assertSame(1, $bottom->y);
        static::assertSame(80, $bottom->width);
        static::assertSame(23, $bottom->height);
    }

    public function testHorizontalFixedAndFill(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$left, $right] = Layout\horizontal($rect, [
            Layout\fixed(20),
            Layout\fill(),
        ]);

        static::assertSame(0, $left->x);
        static::assertSame(20, $left->width);
        static::assertSame(24, $left->height);

        static::assertSame(20, $right->x);
        static::assertSame(60, $right->width);
        static::assertSame(24, $right->height);
    }

    public function testVerticalMultipleFills(): void
    {
        $rect = Rect::fromSize(80, 20);

        [$a, $b] = Layout\vertical($rect, [
            Layout\fill(),
            Layout\fill(),
        ]);

        static::assertSame(10, $a->height);
        static::assertSame(10, $b->height);
    }

    public function testVerticalMaxConstraint(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$top, $bottom] = Layout\vertical($rect, [
            Layout\fill(),
            Layout\max(6, Layout\fixed(3)),
        ]);

        static::assertSame(3, $bottom->height);
        static::assertSame(21, $top->height);
    }

    public function testVerticalEmptyConstraints(): void
    {
        $rect = Rect::fromSize(80, 24);

        $result = Layout\vertical($rect, []);

        static::assertSame([], $result);
    }

    public function testVerticalThreeWaySplit(): void
    {
        $rect = Rect::fromSize(80, 30);

        [$a, $b, $c] = Layout\vertical($rect, [
            Layout\fixed(5),
            Layout\fill(),
            Layout\fixed(3),
        ]);

        static::assertSame(5, $a->height);
        static::assertSame(22, $b->height);
        static::assertSame(3, $c->height);
    }

    public function testHorizontalPreservesY(): void
    {
        $rect = new Rect(0, 5, 80, 10);

        [$left, $right] = Layout\horizontal($rect, [
            Layout\fixed(30),
            Layout\fill(),
        ]);

        static::assertSame(5, $left->y);
        static::assertSame(5, $right->y);
        static::assertSame(10, $left->height);
        static::assertSame(10, $right->height);
    }

    public function testVerticalMinConstraint(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$top, $bottom] = Layout\vertical($rect, [
            Layout\fill(),
            Layout\min(10, Layout\fixed(3)),
        ]);

        static::assertSame(10, $bottom->height);
        static::assertSame(14, $top->height);
    }

    public function testVerticalFixedExceedsTotalSpace(): void
    {
        $rect = Rect::fromSize(80, 10);

        [$a, $b, $c] = Layout\vertical($rect, [
            Layout\fixed(5),
            Layout\fixed(5),
            Layout\fixed(5),
        ]);

        $total = $a->height + $b->height + $c->height;
        static::assertSame(10, $total);
        static::assertGreaterThan(0, $a->height);
        static::assertGreaterThan(0, $b->height);
        static::assertGreaterThan(0, $c->height);
    }

    public function testHorizontalFixedExceedsTotalSpace(): void
    {
        $rect = Rect::fromSize(10, 5);

        [$a, $b] = Layout\horizontal($rect, [
            Layout\fixed(8),
            Layout\fixed(8),
        ]);

        static::assertSame(10, $a->width + $b->width);
        static::assertSame($a->width, $b->width);
    }

    public function testProportionalOverflowDistribution(): void
    {
        $rect = Rect::fromSize(10, 5);

        [$a, $b] = Layout\horizontal($rect, [
            Layout\fixed(12),
            Layout\fixed(4),
        ]);

        static::assertSame(10, $a->width + $b->width);
        static::assertGreaterThan($b->width, $a->width);
    }

    public function testVerticalMinWithFillInner(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$top, $bottom] = Layout\vertical($rect, [
            Layout\fixed(10),
            Layout\min(5, Layout\fill()),
        ]);

        static::assertSame(10, $top->height);
        static::assertSame(14, $bottom->height);
    }

    public function testVerticalMaxWithFillInner(): void
    {
        $rect = Rect::fromSize(80, 24);

        [$top, $bottom] = Layout\vertical($rect, [
            Layout\fixed(10),
            Layout\max(8, Layout\fill()),
        ]);

        static::assertSame(10, $top->height);
        static::assertSame(8, $bottom->height);
    }
}
