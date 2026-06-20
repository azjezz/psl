<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class SliceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSlice(array $expected, array $array, int $n, null|int $l = null): void
    {
        $result = Dict\slice::<string, int>($array, $n, $l);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [['c' => 3, 'd' => 4], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [['b' => 2, 'c' => 3], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 1, 2];
    }

    public function testSliceWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::<string, int>::create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

        static::assertSame(['c' => 3, 'd' => 4], Dict\slice::<string, int>($iterator, 2));
    }

    public function testSliceWithNonArrayIterableAndLength(): void
    {
        $iterator = Iter\Iterator::<string, int>::create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

        static::assertSame(['b' => 2, 'c' => 3], Dict\slice::<string, int>($iterator, 1, 2));
    }

    public function testSliceWithNonArrayIterableZeroLength(): void
    {
        $iterator = Iter\Iterator::<string, int>::create(['a' => 1, 'b' => 2]);

        static::assertSame([], Dict\slice::<string, int>($iterator, 0, 0));
    }
}
