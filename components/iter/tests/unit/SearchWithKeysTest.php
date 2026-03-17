<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchWithKeysTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testSearchSome(string $expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search_with_keys($iterable, $predicate));
    }

    public static function provideDataSome(): iterable
    {
        yield ['baz', ['foo', 'bar', 'baz'], static fn(int $k, string $v): bool => 2 === $k && 'baz' === $v];

        yield [
            'baz',
            Iter\to_iterator(['foo', 'bar', 'baz']),
            static fn(int $k, string $v): bool => 2 === $k && 'baz' === $v,
        ];
    }

    #[DataProvider('provideDataNone')]
    public function testSearchNone(iterable $iterable, callable $predicate): void
    {
        static::assertNull(Iter\search_with_keys($iterable, $predicate));
    }

    public static function provideDataNone(): iterable
    {
        yield [[], static fn(int $_, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator([]), static fn(int $_, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator(['foo', 'bar', 'baz']), static fn(int $_, string $v): bool => 'qux' === $v];
    }
}
