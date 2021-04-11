<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

final class SortByKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortByKey(array $expected, array $array, ?callable $comparator = null): void
    {
        static::assertSame($expected, Arr\sort_by_key($array, $comparator));
    }

    public function provideData(): array
    {
        return [
            [
                ['a' => 'orange', 'b' => 'banana', 'c' => 'apple', 'd' => 'lemon'],
                ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'],
                null,
            ],

            [
                ['d' => 'lemon', 'c' => 'apple', 'b' => 'banana', 'a' => 'orange'],
                ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'],
                static fn (string $a, string $b) => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],
        ];
    }
}
