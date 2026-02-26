<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchWithKeysOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testSearchSome(string $expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search_with_keys_opt($iterable, $predicate)->unwrap());
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
        static::assertTrue(Iter\search_with_keys_opt($iterable, $predicate)->isNone());
    }

    public static function provideDataNone(): iterable
    {
        yield [[], static fn(int $_k, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator([]), static fn(int $_k, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator(['foo', 'bar', 'baz']), static fn(int $_k, string $v): bool => 'qux' === $v];
    }
}
