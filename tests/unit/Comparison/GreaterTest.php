<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Psl\Comparison;
use Psl\Comparison\Order;

class GreaterTest extends AbstractComparisonTest
{
    /**
     * @dataProvider provideComparisonCases
     */
    public function testItCanCheckGreater(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Greater, Comparison\greater($a, $b));
    }
}
