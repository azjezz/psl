<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Vec;

final class IntersectByKeyTest extends TestCase
{
    /**
     * @param iterable<array-key, mixed> $first
     * @param iterable<array-key, mixed> $second
     * @param iterable<array-key, mixed> ...$rest
     */
    #[DataProvider('provideData')]
    public function testIntersectByKey(array $expected, iterable $first, iterable $second, iterable ...$rest): void
    {
        static::assertSame($expected, Dict\intersect_by_key::<int, int>($first, $second, ...$rest));
    }

    public static function provideData(): iterable
    {
        yield [[], [], [], []];
        yield [[], [], [1, 2, 3]];
        yield [[], [], [1, 2, 3], []];
        yield [[], [], [1, 2, 3], [], [4, 5]];

        yield [[], [1, 2], [], []];
        yield [[], [1, 2], ['foo' => 2], []];
        yield [[], [1, 2], [], ['baz' => 1]];
        yield [[], [1, 2], ['foo' => 2], ['baz' => 1]];
        yield [[], [1, 2], ['foo' => 2], ['baz' => 1]];
        yield [[], [1, 2], ['foo' => 2, 'baz' => 1]];
        yield [[1, 2, 3, 4, 5, 6], Vec\range::<int>(1, 8), Vec\range::<int>(1, 6)];
        yield [[], Vec\range::<int>(1, 8), Vec\range::<int>(1, 6), []];
        yield [[5 => 6], Vec\range::<int>(1, 8), Vec\range::<int>(1, 6), [5 => 6, 6 => 7]];
    }
}
