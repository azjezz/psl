<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

class ConcatTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testConcat(array $expected, iterable $first, iterable ...$other): void
    {
        static::assertSame($expected, Arr\concat($first, ...$other));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                ['foo' => 'a'],
                ['bar' => 'b'],
                ['baz' => 'c'],
            ],
            [
                ['foo', 'bar', 'baz', 'qux'],
                new Collection\Map(['foo']),
                new Collection\Vector(['bar']),
                new Collection\Pair('baz', 'qux'),
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
            [
                ['a', 'b', 'c', 'd', 'e'],
                ['a'],
                (fn () => yield 'b')(),
                new Collection\Map(['c']),
                new Collection\Vector(['d']),
                new Collection\ImmVector(['e']),
            ],
        ];
    }
}
