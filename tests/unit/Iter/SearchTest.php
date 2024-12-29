<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class SearchTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSearch($expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\search($iterable, $predicate));
    }

    public function provideData(): iterable
    {
        yield ['baz', ['foo', 'bar', 'baz'], static fn(string $v): bool => 'baz' === $v];
        yield [null, ['foo', 'bar', 'baz'], static fn(string $v): bool => 'qux' === $v];
        yield [null, [], static fn(string $v): bool => 'qux' === $v];

        yield ['baz', Iter\to_iterator(['foo', 'bar', 'baz']), static fn(string $v): bool => 'baz' === $v];
        yield [null, Iter\to_iterator(['foo', 'bar', 'baz']), static fn(string $v): bool => 'qux' === $v];
        yield [null, Iter\to_iterator([]), static fn(string $v): bool => 'qux' === $v];
    }
}
