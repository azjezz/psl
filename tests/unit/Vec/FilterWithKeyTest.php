<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Vec;

final class FilterWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterWithKey(array $expected, iterable $iterable, null|Closure $predicate = null): void
    {
        $result = Vec\filter_with_key($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[], []];
        yield [['a', 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn(int $_k, string $_v): false => false];
        yield [['a', 'b'], ['a', 'b'], static fn(int $_k, string $_v): bool => true];
        yield [[], Collection\Vector::fromArray([])];
        yield [['a', 'b'], Collection\Vector::fromArray(['a', 'b'])];
        yield [[], Collection\Vector::fromArray(['a', 'b']), static fn(int $_k, string $_v): false => false];
        yield [['a', 'b'], Collection\Vector::fromArray(['a', 'b']), static fn(int $_k, string $_v): bool => true];
        yield [['a'], ['a', 'b'], static fn(int $_k, string $v): bool => 'b' !== $v];
        yield [[], ['a', 'b'], static fn(int $k, string $v): bool => 'b' !== $v && 0 !== $k];
        yield [['a'], ['a', 'b'], static fn(int $k, string $v): bool => 'b' !== $v && 1 !== $k];
        yield [[], ['a', 'b'], static fn(int $k, string $v): bool => 'a' !== $v && 1 !== $k];
        yield [['b'], ['a', 'b'], static fn(int $k, string $v): bool => 'a' !== $v && 0 !== $k];
    }
}
