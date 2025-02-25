<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;

final class SortByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, array $array, callable $scalar_fun, null|Closure $comp = null): void
    {
        static::assertSame($expected, Dict\sort_by($array, $scalar_fun, $comp));
    }

    public function provideData(): array
    {
        $a = [1, 2];
        $b = [1, 2, 3, 4];
        $c = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax'];

        $expected = [2 => $a, 0 => $b, 1 => $c];
        $array = [$b, $c, $a];
        $scalar_fun =
            /**
             * @param array<array-key, string|int> $array
             *
             * @pure
             */
            static fn(array $array): int => Iter\count($array);

        return [
            [
                $expected,
                $array,
                $scalar_fun,
            ],
            [
                [1 => 'a', 2 => 'b', 3 => 'c', 0 => 'd'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                ['a'],
                ['a'],
                /**
                 * @pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                [0 => 'd', 3 => 'c', 2 => 'b', 1 => 'a'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @pure
                 */
                static fn(string $v): string => $v,
                /**
                 * @pure
                 */
                static fn(string $a, string $b): int => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],
            [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                /**
                 * @pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                [4 => 'jumped', 0 => 'the', 1 => 'quick', 2 => 'brown', 3 => 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                /**
                 * @pure
                 */
                static fn(string $v): string => Str\Byte\reverse($v),
            ],
        ];
    }
}
