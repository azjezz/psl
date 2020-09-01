<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class SearchTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSearch($expected, iterable $iterable, callable $predicate): void 
    {
        self::assertSame($expected, Iter\search($iterable, $predicate));
    }

    public function provideData(): iterable 
    {
        yield ['baz', ['foo', 'bar', 'baz'], fn(string $v): bool => 'baz' === $v];
        yield [null, ['foo', 'bar', 'baz'], fn(string $v): bool => 'qux' === $v];
        yield [null, [], fn(string $v): bool => 'qux' === $v];

        yield ['baz', Iter\to_iterator(['foo', 'bar', 'baz']), fn(string $v): bool => 'baz' === $v];
        yield [null, Iter\to_iterator(['foo', 'bar', 'baz']), fn(string $v): bool => 'qux' === $v];
        yield [null, Iter\to_iterator([]), fn(string $v): bool => 'qux' === $v];
    }
}
