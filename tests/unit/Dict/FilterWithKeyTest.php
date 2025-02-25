<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class FilterWithKeyTest extends TestCase
{
    /**
     * @template Tk of array-key
     * @template Tv
     *
     * @param array<Tk, Tv> $expected
     * @param iterable<Tk, Tv> $iterable
     * @param (Closure(Tk, Tv): bool)|null $predicate
     *
     * @dataProvider provideData
     */
    public function testFilterWithKey(array $expected, iterable $iterable, null|Closure $predicate = null): void
    {
        $result = Dict\filter_with_key($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[], []];
        yield [['a', 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn(int $_k, string $_v): bool => false];
        yield [['a', 'b'], ['a', 'b'], static fn(int $_k, string $_v): bool => true];
        yield [['a'], ['a', 'b'], static fn(int $_k, string $v): bool => 'b' !== $v];
        yield [[], ['a', 'b'], static fn(int $k, string $v): bool => 'b' !== $v && 0 !== $k];
        yield [['a'], ['a', 'b'], static fn(int $k, string $v): bool => 'b' !== $v && 1 !== $k];
        yield [[], ['a', 'b'], static fn(int $k, string $v): bool => 'a' !== $v && 1 !== $k];
        yield [[1 => 'b'], ['a', 'b'], static fn(int $k, string $v): bool => 'a' !== $v && 0 !== $k];
        yield [
            [1 => 'b'],
            Collection\Vector::fromArray(['a', 'b']),
            static fn(int $k, string $v): bool => 'a' !== $v && 0 !== $k,
        ];
    }
}
