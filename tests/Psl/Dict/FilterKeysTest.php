<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterKeys(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Dict\filter_keys($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [[1 => 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], static fn () => false];
        yield  [['a', 'b'], ['a', 'b'], static fn (int $_): bool => true];
        yield  [['a'], ['a', 'b'], static fn (int $k): bool => 1 !== $k];
        yield  [['a'], Collection\Vector::fromArray(['a', 'b']), static fn (int $k): bool => 1 !== $k];
    }
}
