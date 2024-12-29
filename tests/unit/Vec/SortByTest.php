<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final class SortByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSortBy(array $expected, array $array, callable $scalar_fun, null|callable $comp = null): void
    {
        static::assertSame($expected, Vec\sort_by($array, $scalar_fun, $comp));
    }

    public function provideData(): array
    {
        $a = [1, 2];
        $b = [1, 2, 3, 4];
        $c = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax'];
        $expected = [$a, $b, $c];
        $array = [$b, $c, $a];
        $scalar_fun =
            /**
             * @param array<array-key, string|int> $arr
             *
             * @psalm-pure
             */
            static fn(array $arr): int => Iter\count($arr);

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
                 * @psalm-pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                ['a'],
                ['a'],
                /**
                 * @psalm-pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                ['d', 'c', 'b', 'a'],
                ['d', 'a', 'b', 'c'],
                /**
                 * @psalm-pure
                 */
                static fn(string $v): string => $v,
                /**
                 * @psalm-pure
                 */
                static fn(string $a, string $b): int => Str\ord($a) > Str\ord($b) ? -1 : 1,
            ],
            [
                ['bar', 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
                /**
                 * @psalm-pure
                 */
                static fn(string $v): string => $v,
            ],
            [
                ['jumped', 'the', 'quick', 'brown', 'fox'],
                ['the', 'quick', 'brown', 'fox', 'jumped'],
                /**
                 * @psalm-pure
                 */
                static fn(string $v): string => Str\Byte\reverse($v),
            ],
        ];
    }
}
