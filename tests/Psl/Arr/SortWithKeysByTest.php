<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Str;

class SortWithKeysByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, iterable $iterable, callable $scalar_fun, ?callable $comp = null): void
    {
        self::assertSame($expected, Arr\sort_with_keys_by($iterable, $scalar_fun, $comp));
    }

    public function provideData(): array
    {
        $a = new Collection\Vector([1, 2]);
        $b = new Collection\MutableVector([1, 2, 3, 4]);
        $c = new Collection\Map(['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax']);
        $expected = [2 => $a, 0 => $b, 1 => $c];
        $iterable = [$b, $c, $a];
        $scalar_fun = fn (Collection\CollectionInterface $collection) => $collection->count();

        return [
            [
                $expected,
                $iterable,
                $scalar_fun,
            ],

            [
                [1 => 'a', 2 => 'b', 3 => 'c', 0 => 'd'],
                ['d', 'a', 'b', 'c'],
                fn ($v) => $v,
            ],

            [
                ['a'],
                ['a'],
                fn ($v) => $v,
            ],

            [
                [0 => 'd', 3 => 'c', 2 => 'b', 1 => 'a'],
                ['d', 'a', 'b', 'c'],
                fn ($v) => $v,
                fn (string $a, string $b) => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],

            [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                fn ($v) => $v,
            ],

            [
                [4 => 'jumped', 0 => 'the', 1 => 'quick', 2 => 'brown', 3 => 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                fn ($v) => Str\Byte\reverse($v),
            ],
        ];
    }
}
