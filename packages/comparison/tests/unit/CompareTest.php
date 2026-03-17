<?php

declare(strict_types=1);

namespace Psl\Comparison\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Comparison;
use Psl\Comparison\Order;

class CompareTest extends AbstractComparisonTestCase
{
    #[DataProvider('provideComparisonCases')]
    public function testItCanCompare(mixed $a, mixed $b, Order $expected): void
    {
        static::assertSame($expected, Comparison\compare($a, $b));
    }

    public function testItCanFailComparing(): void
    {
        $a = self::createIncomparableWrapper(1);
        $b = self::createIncomparableWrapper(2);

        $this->expectException(Comparison\Exception\IncomparableException::class);
        $this->expectExceptionMessage('Unable to compare "int" with "int".');

        Comparison\compare($a, $b);
    }

    public function testOrderDefault(): void
    {
        static::assertSame(Order::Equal, Order::default());
    }

    public function testItCanFailComparingWithAdditionalInfo(): void
    {
        $a = self::createIncomparableWrapper(1, 'Can only compare even numbers');
        $b = self::createIncomparableWrapper(2);

        $this->expectException(Comparison\Exception\IncomparableException::class);
        $this->expectExceptionMessage('Unable to compare "int" with "int": Can only compare even numbers');

        Comparison\compare($a, $b);
    }
}
