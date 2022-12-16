<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Range;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Range;

final class BetweenRangeTest extends TestCase
{
    public function testContains(): void
    {
        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\UINT8_MAX));
        static::assertTrue($range->contains(Math\INT8_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX - 1));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MIN));
        static::assertFalse($range->contains(Math\INT53_MIN));
        static::assertFalse($range->contains(Math\INT64_MIN));
        static::assertFalse($range->contains(-Math\INFINITY));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));

        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: false);

        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX - 1));
        static::assertTrue($range->contains(Math\INT16_MIN));

        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: true);

        static::assertTrue($range->contains(Math\INT16_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX - 1));
        static::assertTrue($range->contains(Math\INT16_MIN));
        
        $range = new Range\BetweenRange(Math\INT16_MIN, Math\INT16_MAX);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\UINT8_MAX));
        static::assertTrue($range->contains(Math\INT8_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX - 1));
        static::assertFalse($range->contains(Math\INT16_MAX));
        static::assertFalse($range->contains(Math\UINT16_MAX));
        static::assertFalse($range->contains(Math\INT32_MIN));
        static::assertFalse($range->contains(Math\INT53_MIN));
        static::assertFalse($range->contains(Math\INT64_MIN));
        static::assertFalse($range->contains(-Math\INFINITY));
        static::assertFalse($range->contains(Math\INT32_MAX));
        static::assertFalse($range->contains(Math\INT53_MAX));
        static::assertFalse($range->contains(Math\INT64_MAX));

        $range = new Range\BetweenRange(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: true);

        static::assertTrue($range->contains(Math\INT16_MAX));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN + 1));
    }
    
    public function testBounds(): void
    {
        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());

        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: false);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());

        $range = Range\between(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: true);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());
        
        $range = new Range\BetweenRange(Math\INT16_MIN, Math\INT16_MAX);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());

        $range = new Range\BetweenRange(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: false);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());

        $range = new Range\BetweenRange(Math\INT16_MIN, Math\INT16_MAX, upper_inclusive: true);
        static::assertSame(Math\INT16_MIN, $range->getLowerBound());
        static::assertSame(Math\INT16_MAX, $range->getUpperBound());
    }

    public function testIsInclusive(): void
    {
        $range = Range\between(0, 100, upper_inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = Range\between(0, 100, upper_inclusive: false);
        static::assertFalse($range->isUpperInclusive());

        $range = Range\between(0, 100);
        static::assertFalse($range->isUpperInclusive());

        $range = new Range\BetweenRange(0, 100, upper_inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = new Range\BetweenRange(0, 100, upper_inclusive: false);
        static::assertFalse($range->isUpperInclusive());

        $range = new Range\BetweenRange(0, 100, upper_inclusive: true);
        static::assertTrue($range->isUpperInclusive());

        $range = new Range\BetweenRange(0, 100);
        static::assertFalse($range->isUpperInclusive());
    }

    public function testIterate(): void
    {
        $range = Range\between(1, 10);
        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $actual = [];
        foreach ($range as $value) {
            $actual[] = $value;
        }

        static::assertSame($expected, $actual);

        $range = Range\between(1, 10, upper_inclusive: true);
        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $actual = [];
        foreach ($range as $value) {
            $actual[] = $value;
        }

        static::assertSame($expected, $actual);

        $range = new Range\BetweenRange(1, 10);
        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $actual = [];
        foreach ($range as $value) {
            $actual[] = $value;
        }

        static::assertSame($expected, $actual);

        $range = new Range\BetweenRange(1, 10, upper_inclusive: true);
        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $actual = [];
        foreach ($range as $value) {
            $actual[] = $value;
        }

        static::assertSame($expected, $actual);
    }
}
