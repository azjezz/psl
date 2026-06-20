<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testSearchSome(string $expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search_opt::<string>($iterable, $predicate)->unwrap());
    }

    public static function provideDataSome(): iterable
    {
        yield ['baz', ['foo', 'bar', 'baz'], static fn(string $v): bool => 'baz' === $v];

        yield ['baz', Iter\to_iterator::<int, string>(['foo', 'bar', 'baz']), static fn(string $v): bool => 'baz' === $v];
    }

    #[DataProvider('provideDataNone')]
    public function testSearchNone(iterable $iterable, callable $predicate): void
    {
        static::assertTrue(Iter\search_opt::<string>($iterable, $predicate)->isNone());
    }

    public static function provideDataNone(): iterable
    {
        yield [[], static fn(string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator::<int, string>([]), static fn(string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator::<int, string>(['foo', 'bar', 'baz']), static fn(string $v): bool => 'qux' === $v];
    }
}
