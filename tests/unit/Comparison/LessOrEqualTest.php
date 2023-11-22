<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Psl\Comparison;
use Psl\Comparison\Order;

class LessOrEqualTest extends AbstractComparisonTest
{
    /**
     * @dataProvider provideComparisonCases
     */
    public function testItCanCheckLessOrEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Less || $expected === Order::Equal, Comparison\less_or_equal($a, $b));
    }
}
