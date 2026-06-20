<?php

declare(strict_types=1);

namespace Psl\Comparison\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class SortTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanSort(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected->value, Comparison\sort::<mixed>($a, $b));
    }
}
