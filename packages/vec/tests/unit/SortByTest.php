<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final class SortByTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSortBy(array $expected, array $array, callable $scalarFun, null|callable $comp = null): void
    {
        static::assertSame($expected, Vec\sort_by($array, $scalarFun, $comp));
    }

    public static function provideData(): array
    {
        $a = [1, 2];
        $b = [1, 2, 3, 4];
        $c = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux', 'e' => 'lax'];
        $expected = [$a, $b, $c];
        $array = [$b, $c, $a];
        $scalarFun =
            /**
             * @param array<array-key, string|int> $arr
             *
             * @psalm-pure
             */
            Iter\count(...);

        return [
            [
                $expected,
                $array,
                $scalarFun,
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
                Str\Byte\reverse(...),
            ],
        ];
    }
}
