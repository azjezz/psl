<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class FilterWithKeyTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $expected
     * @param iterable<array-key, mixed> $iterable
     * @param (Closure(array-key, mixed): bool)|null $predicate
     */
    #[DataProvider('provideData')]
    public function testFilterWithKey(array $expected, iterable $iterable, null|Closure $predicate = null): void
    {
        $result = Dict\filter_with_key::<int, string>($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
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
            Collection\Vector::<string>::fromArray(['a', 'b']),
            static fn(int $k, string $v): bool => 'a' !== $v && 0 !== $k,
        ];
    }
}
