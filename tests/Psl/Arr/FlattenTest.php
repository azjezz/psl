<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

class FlattenTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFlatten(array $expected, iterable $iterables): void
    {
        self::assertSame($expected, Arr\flatten($iterables));
    }

    public function provideData(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 'c' => 'd', 'd' => 'e'],

                new Collection\Vector([
                    new Collection\Map(['a' => 'foo', 'b' => 'bar']),
                    ['a' => 'b'],
                    new Collection\ImmMap(['b' => 'c', 'c' => 'd']),
                    (fn () => yield 'd' => 'baz')(),
                    (fn () => yield 'd' => 'e')(),
                ]),
            ],

            [
                [1, 2, 9, 8],
                [
                    [0 => 1, 1 => 2],
                    [2 => 9, 3 => 8],
                ],
            ],
        ];
    }
}
