<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchOptTest extends TestCase
{
    /**
     * @dataProvider provideDataSome
     */
    public function testSearchSome($expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search_opt($iterable, $predicate)->unwrap());
    }

    public function provideDataSome(): iterable
    {
        yield ['baz', ['foo', 'bar', 'baz'], static fn (string $v): bool => 'baz' === $v];

        yield ['baz', Iter\to_iterator(['foo', 'bar', 'baz']), static fn (string $v): bool => 'baz' === $v];
    }
    /**
     * @dataProvider provideDataNone
     */
    public function testSearchNone(iterable $iterable, callable $predicate): void
    {
        static::assertTrue(Iter\search_opt($iterable, $predicate)->isNone());
    }
    public function provideDataNone(): iterable
    {
        yield [[], static fn (string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator([]), static fn (string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator(['foo', 'bar', 'baz']), static fn (string $v): bool => 'qux' === $v];
    }
}
