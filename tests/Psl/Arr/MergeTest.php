<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class MergeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMerge(array $expected, array $array, array ...$arrays): void
    {
        static::assertSame($expected, Arr\merge($array, ...$arrays));
    }

    public function provideData(): array
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
                [1, 2, 9, 8],
                [0 => 1, 1 => 2],
                [2 => 9, 3 => 8],
            ],
        ];
    }
}
