<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class SortTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSort(array $expected, array $array, null|Closure $comparator = null): void
    {
        static::assertSame($expected, Dict\sort::<string|int, string|int>($array, $comparator));
    }

    public static function provideData(): array
    {
        return [
            [
                [1 => 'a', 2 => 'b', 0 => 'c'],
                ['c', 'a', 'b'],
            ],
            [
                [8, 9, 10],
                [8, 9, 10],
                /**
                 * @param int $a
                 * @param int $b
                 *
                 * @return int
                 *
                 * @pure
                 */
                static fn(int $a, int $b): int => $a <=> $b ? -1 : 1,
            ],
            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }

    public function testSortWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::<string, int>::create(['c' => 3, 'a' => 1, 'b' => 2]);

        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\sort::<string, int>($iterator));
    }
}
