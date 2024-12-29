<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class MergeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMerge(array $expected, array $array, array ...$arrays): void
    {
        static::assertSame($expected, Dict\merge($array, ...$arrays));
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
                [0 => 'b', '1000' => 'b', 'c' => 'c'],
                [0 => 'a'],
                [0 => 'b'],
                ['1000' => 'a'],
                ['1000' => 'b'],
                ['c' => 'c'],
            ],
            [
                [1, 2, 9, 8],
                [0 => 1, 1 => 2],
                [2 => 9, 3 => 8],
            ],
        ];
    }
}
