<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Str;

/**
 * @covers \Psl\Arr\sort_by
 */
class SortByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, iterable $iterable, callable $scalar_fun, ?callable $comp = null): void
    {
        self::assertSame($expected, Arr\sort_by($iterable, $scalar_fun, $comp));
    }

    public function provideData(): array
    {
        $a = new Collection\Vector([1, 2]);
        $b = new Collection\ImmVector([1, 2, 3, 4]);
        $c = new Collection\Map(['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax']);
        $expected = [$a, $b, $c];
        $iterable = [$b, $c, $a];
        $scalar_fun = fn (Collection\ConstCollection $collection) => $collection->count();

        return [
            [
                $expected,
                $iterable,
                $scalar_fun,
            ],

            [
                ['a', 'b', 'c', 'd'],
                ['d', 'a', 'b', 'c'],
                fn ($v) => $v,
            ],

            [
                ['a'],
                ['a'],
                fn ($v) => $v,
            ],

            [
                ['d', 'c', 'b', 'a'],
                ['d', 'a', 'b', 'c'],
                fn ($v) => $v,
                fn (string $a, string $b) => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],

            [
                ['bar', 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                fn ($v) => $v,
            ],

            [
                ['jumped', 'the', 'quick', 'brown', 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                fn ($v) => Str\Byte\reverse($v),
            ],
        ];
    }
}
