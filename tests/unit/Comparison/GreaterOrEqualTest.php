<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Psl\Comparison;
use Psl\Comparison\Order;

class GreaterOrEqualTest extends AbstractComparisonTest
{
    /**
     * @dataProvider provideComparisonCases
     */
    public function testItCanCheckGreaterOrEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame(
            $expected === Order::Greater || $expected === Order::Equal,
            Comparison\greater_or_equal($a, $b),
        );
    }
}
