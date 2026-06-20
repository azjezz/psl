<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class MergeTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMerge(array $expected, array $array, array ...$arrays): void
    {
        static::assertSame($expected, Dict\merge::<string|int, string|int>($array, ...$arrays));
    }

    public static function provideData(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 'c' => 'd', 'd' => 'e'],
                ['a' => 'foo', 'b' => 'bar'],
                ['a' => 'b'],
                ['b' => 'c', 'c' => 'd'],
                ['d' => 'baz'],
                ['d' => 'e'],
            ],
            [
                [0 => 'b', '1000' => 'b', 'c' => 'c'],
                [0 => 'a'],
                [0 => 'b'],
                ['1000' => 'a'],
                ['1000' => 'b'],
                ['c' => 'c'],
            ],
            [
                [1, 2, 9, 8],
                [0 => 1, 1 => 2],
                [2 => 9, 3 => 8],
            ],
        ];
    }

    public function testMergeWithMixedArrayAndNonArrayRest(): void
    {
        $iterator = Iter\Iterator::<string, string>::create(['c' => 'd']);
        $result = Dict\merge::<string, string>(['a' => 'b'], $iterator);

        static::assertSame(['a' => 'b', 'c' => 'd'], $result);
    }

    public function testMergeWithNonArrayFirst(): void
    {
        $iterator = Iter\Iterator::<string, string>::create(['a' => 'b']);
        $result = Dict\merge::<string, string>($iterator, ['c' => 'd']);

        static::assertSame(['a' => 'b', 'c' => 'd'], $result);
    }
}
