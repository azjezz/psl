<?php

declare(strict_types=1);

namespace Psl\Comparison\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class LessOrEqualTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanCheckLessOrEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Less || $expected === Order::Equal, Comparison\less_or_equal::<mixed>($a, $b));
    }
}
