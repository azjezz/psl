<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;

final class SortByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, array $array, callable $scalar_fun, null|callable $comp = null): void
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
             * @return int
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
                 * @param string $v
                 *
                 * @return string
                 *
                 * @pure
                 */
                static fn($v) => $v,
            ],
            [
                ['a'],
                ['a'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @pure
                 */
                static fn($v) => $v,
            ],
            [
                [0 => 'd', 3 => 'c', 2 => 'b', 1 => 'a'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @pure
                 */
                static fn($v) => $v,
                /**
                 * @param string $a
                 * @param string $b
                 *
                 * @return int
                 *
                 * @pure
                 */
                static fn(string $a, string $b) => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],
            [
                ['foo' => 'bar', 'baz' => 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @pure
                 */
                static fn($v) => $v,
            ],
            [
                [4 => 'jumped', 0 => 'the', 1 => 'quick', 2 => 'brown', 3 => 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @pure
                 */
                static fn($v) => Str\Byte\reverse($v),
            ],
        ];
    }
}
