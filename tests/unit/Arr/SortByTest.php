<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

final class SortByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, array $array, callable $scalar_fun, ?callable $comp = null): void
    {
        static::assertSame($expected, Arr\sort_by($array, $scalar_fun, $comp));
    }

    public function provideData(): array
    {
        $a          = [1, 2];
        $b          = [1, 2, 3, 4];
        $c          = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax'];
        $expected   = [$a, $b, $c];
        $array      = [$b, $c, $a];
        $scalar_fun =
            /**
             * @param array<array-key, string|int> $arr
             *
             * @return int
             *
             * @psalm-pure
             */
            static fn ($arr) => Arr\count($arr);

        return [
            [
                $expected,
                $array,
                $scalar_fun,
            ],

            [
                ['a', 'b', 'c', 'd'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @psalm-pure
                 */
                static fn ($v) => $v,
            ],

            [
                ['a'],
                ['a'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @psalm-pure
                 */
                static fn ($v) => $v,
            ],

            [
                ['d', 'c', 'b', 'a'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @psalm-pure
                 */
                static fn ($v) => $v,
                /**
                 * @param string $a
                 * @param string $b
                 *
                 * @return int
                 *
                 * @psalm-pure
                 */
                static fn (string $a, string $b) => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],

            [
                ['bar', 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @psalm-pure
                 */
                static fn ($v) => $v,
            ],

            [
                ['jumped', 'the', 'quick', 'brown', 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                /**
                 * @param string $v
                 *
                 * @return string
                 *
                 * @psalm-pure
                 */
                static fn ($v) => Str\Byte\reverse($v),
            ],
        ];
    }
}
