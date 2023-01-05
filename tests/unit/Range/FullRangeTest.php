<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Range;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Range;

final class FullRangeTest extends TestCase
{
    public function testContains(): void
    {
        $range = Range\full();

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(99));
        static::assertTrue($range->contains(1));
        static::assertTrue($range->contains(Math\INT8_MIN));
        static::assertTrue($range->contains(Math\INT16_MIN));
        static::assertTrue($range->contains(Math\INT32_MIN));
        static::assertTrue($range->contains(Math\INT53_MIN));
        static::assertTrue($range->contains(Math\INT64_MIN));
        static::assertTrue($range->contains(Math\INT8_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX));
        static::assertTrue($range->contains(Math\INT32_MAX));
        static::assertTrue($range->contains(Math\INT53_MAX));
        static::assertTrue($range->contains(Math\INT64_MAX));
        static::assertTrue($range->contains(Math\UINT8_MAX));
        static::assertTrue($range->contains(Math\UINT16_MAX));
        static::assertTrue($range->contains(Math\UINT32_MAX));
    }

    public function testWithers(): void
    {
        $range = new Range\FullRange();

        static::assertSame(0, $range->withLowerBound(0)->getLowerBound());
        static::assertSame(1, $range->withUpperBound(1, false)->getUpperBound());
        static::assertSame(1, $range->withUpperBoundExclusive(1)->getUpperBound());
        static::assertSame(1, $range->withUpperBoundInclusive(1)->getUpperBound());
        static::assertSame(false, $range->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(false, $range->withUpperBound(0, true)->withUpperInclusive(false)->isUpperInclusive());
        static::assertSame(true, $range->withUpperBound(0, false)->withUpperInclusive(true)->isUpperInclusive());
        static::assertSame(false, $range->withUpperBound(0, false)->isUpperInclusive());
        static::assertSame(true, $range->withUpperBound(0, true)->isUpperInclusive());
        static::assertSame(false, $range->withUpperBoundExclusive(0)->isUpperInclusive());
        static::assertSame(true, $range->withUpperBoundInclusive(0)->isUpperInclusive());
    }
}
