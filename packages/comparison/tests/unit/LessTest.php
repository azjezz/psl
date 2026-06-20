<?php

declare(strict_types=1);

namespace Psl\Comparison\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class LessTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanCheckLess(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected === Order::Less, Comparison\less::<mixed>($a, $b));
    }
}
