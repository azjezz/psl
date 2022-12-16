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
        static::assertTrue($range->contains(-Math\INFINITY));
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
    }
}
