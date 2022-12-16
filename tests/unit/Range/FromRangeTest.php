<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Range;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Range;

final class FromRangeTest extends TestCase
{
    public function testContains(): void
    {
        $range = Range\from(100);

        static::assertTrue($range->contains(100));
        static::assertTrue($range->contains(Math\INT8_MAX));
        static::assertTrue($range->contains(Math\INT16_MAX));
        static::assertTrue($range->contains(Math\INT32_MAX));
        static::assertTrue($range->contains(Math\INT53_MAX));
        static::assertTrue($range->contains(Math\INT64_MAX));
        static::assertTrue($range->contains(Math\UINT8_MAX));
        static::assertTrue($range->contains(Math\UINT16_MAX));
        static::assertTrue($range->contains(Math\UINT32_MAX));
        static::assertTrue($range->contains(Math\UINT64_MAX));
        static::assertTrue($range->contains(Math\INFINITY));

        static::assertFalse($range->contains(99));
        static::assertFalse($range->contains(1));
        static::assertFalse($range->contains(Math\INT8_MIN));
        static::assertFalse($range->contains(Math\INT16_MIN));
        static::assertFalse($range->contains(Math\INT32_MIN));
        static::assertFalse($range->contains(Math\INT53_MIN));
        static::assertFalse($range->contains(Math\INT64_MIN));
    }
    
    public function testLowerBound(): void
    {
        $range = Range\from(10);
        static::assertSame(10, $range->getLowerBound());

        $range = Range\from(2);
        static::assertSame(2, $range->getLowerBound());

        $range = Range\from(-24.24);
        static::assertSame(-24.24, $range->getLowerBound());
    }
    
    public function testIterate(): void
    {
        $range = Range\from(10);
        $last = null;
        foreach ($range as $value) {
            if (null !== $last) {
                static::assertSame($last + 1, $value);
            } else {
                static::assertSame(10, $value);
            }

            $last = $value;
            // break after the 3rd iteration, otherwise we will be here forever.
            if ($value === 13) {
                break;
            }
        }
        
        $range = Range\from(-24.24);
        $last = null;
        foreach ($range as $value) {
            if (null !== $last) {
                static::assertSame($last + 1, $value);
            } else {
                static::assertSame(-24.24, $value);
            }

            $last = $value;
            // break after the 3rd iteration, otherwise we will be here forever.
            if ($value === -21.24) {
                break;
            }
        }
    }
}
