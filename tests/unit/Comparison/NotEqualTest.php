<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Psl\Comparison;
use Psl\Comparison\Order;

class NotEqualTest extends AbstractComparisonTest
{
    /**
     * @dataProvider provideComparisonCases
     */
    public function testItCanNotEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected !== Order::Equal, Comparison\not_equal($a, $b));
    }
}
