<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class SortTest extends TestCase
{
    /**
     * @template T
     *
     * @param list<T> $expected
     * @param (callable(T, T): int)|null $comparator
     *
     * @dataProvider provideData
     */
    public function testSort(array $expected, array $array, null|callable $comparator = null): void
    {
        static::assertSame($expected, Vec\sort($array, $comparator));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                ['c', 'a', 'b'],
            ],
            [
                [8, 9, 10],
                [8, 9, 10],
                /**
                 * @psalm-pure
                 */
                static fn(int $a, int $b): int => $a <=> $b ? -1 : 1,
            ],
            [
                ['bar', 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
