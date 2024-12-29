<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class SortTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSort(array $expected, array $array, null|callable $comparator = null): void
    {
        static::assertSame($expected, Dict\sort($array, $comparator));
    }

    public function provideData(): array
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
                static fn(int $a, int $b) => $a <=> $b ? -1 : 1,
            ],
            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
