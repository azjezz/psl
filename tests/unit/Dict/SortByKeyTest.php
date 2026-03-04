<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;

final class SortByKeyTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSortByKey(array $expected, array $array, null|Closure $comparator = null): void
    {
        static::assertSame($expected, Dict\sort_by_key($array, $comparator));
    }

    public static function provideData(): array
    {
        return [
            [
                ['a' => 'orange', 'b' => 'banana', 'c' => 'apple', 'd' => 'lemon'],
                ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'],
                null,
            ],
            [
                ['d' => 'lemon', 'c' => 'apple', 'b' => 'banana', 'a' => 'orange'],
                ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'],
                static fn(string $a, string $b): int => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],
        ];
    }

    public function testSortByKeyWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::create(['c' => 3, 'a' => 1, 'b' => 2]);

        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\sort_by_key($iterator));
    }
}
