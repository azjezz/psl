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
        static::assertTrue($range->contains(-Math\INFINITY));

        static::assertFalse($range->contains(Math\INT8_MAX));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));
        static::assertFalse($range->contains(Math\UINT8_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\UINT32_MAX));
        static::assertFalse($range->contains(Math\UINT64_MAX));
        static::assertFalse($range->contains(Math\INFINITY));

        $range = Range\to(100, inclusive: false);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(-Math\INFINITY));

        $range = Range\to(100);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(-Math\INFINITY));
        
        $range = new Range\ToRange(100, upper_inclusive: true);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(-Math\INFINITY));

        static::assertFalse($range->contains(Math\INT8_MAX));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));
        static::assertFalse($range->contains(Math\UINT8_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\UINT32_MAX));
        static::assertFalse($range->contains(Math\UINT64_MAX));
        static::assertFalse($range->contains(Math\INFINITY));

        $range = new Range\ToRange(100, upper_inclusive: false);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(-Math\INFINITY));

        $range = new Range\ToRange(100);

        static::assertFalse($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(-Math\INFINITY));
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
    
    public function testUpperBound(): void
    {
        $range = Range\to(10, inclusive: true);
        static::assertSame(10, $range->getUpperBound());

        $range = Range\to(2, inclusive: true);
        static::assertSame(2, $range->getUpperBound());

        $range = Range\to(-24.24, inclusive: true);
        static::assertSame(-24.24, $range->getUpperBound());
    }
}
