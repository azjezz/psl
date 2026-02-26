<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class NotEqualTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanNotEqual(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected !== Order::Equal, Comparison\not_equal($a, $b));
    }
}
