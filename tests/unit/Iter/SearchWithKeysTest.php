<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideDataSome
     */
    public function testSearchSome($expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search_with_keys($iterable, $predicate));
    }

    public function provideDataSome(): iterable
    {
        yield ['baz', ['foo', 'bar', 'baz'], static fn (int $k, string $v): bool => 2 === $k && 'baz' === $v];

        yield [
            'baz',
            Iter\to_iterator(['foo', 'bar', 'baz']), static fn (int $k, string $v): bool => 2 === $k && 'baz' === $v
        ];
    }
    /**
     * @dataProvider provideDataNone
     */
    public function testSearchNone(iterable $iterable, callable $predicate): void
    {
        static::assertNull(Iter\search_with_keys($iterable, $predicate));
    }

    public function provideDataNone(): iterable
    {
        yield [[], static fn (int $k, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator([]), static fn (int $k, string $v): bool => 'qux' === $v];
        yield [Iter\to_iterator(['foo', 'bar', 'baz']), static fn (int $k, string $v): bool => 'qux' === $v];
    }
}
