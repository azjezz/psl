<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Iter;

class SortTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSort(array $expected, iterable $iterable, ?callable $comparator = null): void
    {
        static::assertSame($expected, Arr\sort($iterable, $comparator));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                new Collection\Vector(['c', 'a', 'b']),
            ],

            [
                [8, 9, 10],
                new Collection\MutableVector(Iter\range(8, 10)),
                fn (int $a, int $b) => $a <=> $b ? -1 : 1,
            ],

            [
                ['bar', 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
