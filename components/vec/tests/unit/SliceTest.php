<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class SliceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSlice(array $expected, array $array, int $n, null|int $l = null): void
    {
        $result = Vec\slice($array, $n, $l);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[0, 1, 2, 3, 4, 5], [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5];
        yield [[0, 1, 2], [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3];
    }

    public function testSliceWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3, 4, 5]);

        static::assertSame([3, 4, 5], Vec\slice($iterator, 2));
    }

    public function testSliceWithNonArrayIterableAndLength(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3, 4, 5]);

        static::assertSame([2, 3], Vec\slice($iterator, 1, 2));
    }

    public function testSliceWithNonArrayIterableZeroLength(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3]);

        static::assertSame([], Vec\slice($iterator, 0, 0));
    }
}
