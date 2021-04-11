<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class FilterWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterWithKey(array $expected, array $array, ?callable $predicate = null): void
    {
        $result = Arr\filter_with_key($array, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [['a', 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], static fn (int $_k, string $_v) => false];
        yield  [['a', 'b'], ['a', 'b'], static fn (int $_k, string $_v): bool => true];
        yield  [['a'], ['a', 'b'], static fn (int $k, string $v): bool => 'b' !== $v];
        yield  [[], ['a', 'b'], static fn (int $k, string $v): bool => 'b' !== $v && 0 !== $k];
        yield  [['a'], ['a', 'b'], static fn (int $k, string $v): bool => 'b' !== $v && 1 !== $k];
        yield  [[], ['a', 'b'], static fn (int $k, string $v): bool => 'a' !== $v && 1 !== $k];
        yield  [[1 => 'b'], ['a', 'b'], static fn (int $k, string $v): bool => 'a' !== $v && 0 !== $k];
    }
}
