<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Psl\Comparison;
use Psl\Comparison\Order;

class SortTest extends AbstractComparisonTest
{
    /**
     * @dataProvider provideComparisonCases
     */
    public function testItCanSort(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected->value, Comparison\sort($a, $b));
    }
}
