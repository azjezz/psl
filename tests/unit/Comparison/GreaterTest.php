<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class GreaterTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanCheckGreater(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Greater, Comparison\greater($a, $b));
    }
}
