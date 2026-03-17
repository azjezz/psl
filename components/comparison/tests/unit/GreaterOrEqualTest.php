<?php

declare(strict_types=1);

namespace Psl\Comparison\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class GreaterOrEqualTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanCheckGreaterOrEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Greater || $expected === Order::Equal, Comparison\greater_or_equal(
            $a,
            $b,
        ));
    }
}
