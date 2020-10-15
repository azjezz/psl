<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class SortTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSort(array $expected, array $array, ?callable $comparator = null): void
    {
        static::assertSame($expected, Arr\sort($array, $comparator));
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
                 * @param int $a
                 * @param int $b
                 *
                 * @return int
                 *
                 * @psalm-pure
                 */
                static fn (int $a, int $b) => $a <=> $b ? -1 : 1,
            ],

            [
                ['bar', 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
