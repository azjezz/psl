<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class SortWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSort(array $expected, array $array, ?callable $comparator = null): void
    {
        static::assertSame($expected, Arr\sort_with_keys($array, $comparator));
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
                static fn (int $a, int $b) => $a <=> $b ? -1 : 1,
            ],

            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
