<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

class ContainsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testContains(bool $expected, iterable $iterable, $value): void
    {
        static::assertSame($expected, Arr\contains($iterable, $value));
    }

    public function provideData(): array
    {
        return [
            [true, [1, 2], 1],
            [true, new Collection\Vector([1, 2, 3, 4]), 4],
            [false, new Collection\Vector([1, 2, 3, 4]), 5],
            [true, new Collection\Map(['a' => 'b']), 'b'],
            [true, new Collection\Pair('a', 'b'), 'a'],
            [true, new Collection\Pair('a', 'b'), 'b'],
            [false, new Collection\Pair('a', 'b'), 'c'],
            [true, (fn () => yield 'a')(), 'a'],
            [false, (fn () => yield 'a')(), 'b'],
            [false, ['0'], 0],
        ];
    }
}
