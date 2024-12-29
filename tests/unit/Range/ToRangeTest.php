<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Range;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Range;

final class ToRangeTest extends TestCase
{
    public function testContains(): void
    {
        $range = Range\to(100, inclusive: true);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));

        static::assertFalse($range->contains(Math\INT8_MAX));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));
        static::assertFalse($range->contains(Math\UINT8_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\UINT32_MAX));

        $range = Range\to(100, inclusive: false);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));

        $range = Range\to(100);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));

        $range = new Range\ToRange(100, upper_inclusive: true);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));

        static::assertFalse($range->contains(Math\INT8_MAX));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));
        static::assertFalse($range->contains(Math\UINT8_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\UINT32_MAX));

        $range = new Range\ToRange(100, upper_inclusive: false);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));

        $range = new Range\ToRange(100);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
    }

    public function testIsInclusive(): void
    {
        $range = Range\to(100, inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = Range\to(100, inclusive: false);
        static::assertFalse($range->isUpperInclusive());

        $range = Range\to(100);
        static::assertFalse($range->isUpperInclusive());

        $range = new Range\ToRange(100, upper_inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = new Range\ToRange(100, upper_inclusive: false);
        static::assertFalse($range->isUpperInclusive());

        $range = new Range\ToRange(100, upper_inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = new Range\ToRange(100);
        static::assertFalse($range->isUpperInclusive());
    }

    public function testWithers(): void
    {
        $range = new Range\ToRange(100);

        static::assertSame(100, $range->getUpperBound());
        static::assertSame(0, $range->withLowerBound(0)->getLowerBound());
        static::assertSame(1, $range->withUpperBound(1, false)->getUpperBound());
        static::assertSame(1, $range->withUpperBoundExclusive(1)->getUpperBound());
        static::assertSame(1, $range->withUpperBoundInclusive(1)->getUpperBound());
        static::assertSame(false, $range->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(false, $range->withUpperInclusive(false)->isUpperInclusive());
        static::assertSame(true, $range->withUpperInclusive(true)->isUpperInclusive());
        static::assertSame(false, $range->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(true, $range->withUpperBound(0, true)->isUpperInclusive());
        static::assertSame(false, $range->withUpperBoundExclusive(0)->isUpperInclusive());
        static::assertSame(true, $range->withUpperBoundInclusive(0)->isUpperInclusive());

        static::assertSame(0, $range->withoutUpperBound()->withLowerBound(0)->getLowerBound());
        static::assertSame(1, $range->withoutUpperBound()->withUpperBound(1, false)->getUpperBound());
        static::assertSame(1, $range->withoutUpperBound()->withUpperBoundExclusive(1)->getUpperBound());
        static::assertSame(1, $range->withoutUpperBound()->withUpperBoundInclusive(1)->getUpperBound());
        static::assertSame(false, $range->withoutUpperBound()->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(false, $range->withoutUpperBound()->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(true, $range->withoutUpperBound()->withUpperBound(0, true)->isUpperInclusive());
        static::assertSame(false, $range->withoutUpperBound()->withUpperBoundExclusive(0)->isUpperInclusive());
        static::assertSame(true, $range->withoutUpperBound()->withUpperBoundInclusive(0)->isUpperInclusive());
    }

    public function testUpperBound(): void
    {
        $range = Range\to(10, inclusive: true);
        static::assertSame(10, $range->getUpperBound());

        $range = Range\to(2, inclusive: true);
        static::assertSame(2, $range->getUpperBound());
    }
}
