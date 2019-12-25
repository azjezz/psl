<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Iter;

class SortWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSort(array $expected, iterable $iterable, ?callable $comparator = null): void
    {
        static::assertSame($expected, Arr\sort_with_keys($iterable, $comparator));
    }

    public function provideData(): array
    {
        return [
            [
                [1 => 'a', 2 => 'b', 0 => 'c'],
                new Collection\Vector(['c', 'a', 'b']),
            ],

            [
                [8, 9, 10],
                new Collection\ImmVector(Iter\range(8, 10)),
                fn (int $a, int $b) => $a <=> $b ? -1 : 1,
            ],

            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
