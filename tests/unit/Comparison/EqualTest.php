<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class EqualTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Equal, Comparison\equal($a, $b));
    }
}
