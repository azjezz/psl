<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Vec;

final class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilter(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Vec\filter_keys($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [['b'], ['a', 'b']];
        yield  [['a'], ['a', 'b'], static fn (int $k): bool => $k !== 1];
        yield  [['b'], ['a', 'b'], static fn (int $k): bool => $k !== 0];
        yield  [['b'], Collection\Vector::fromArray(['a', 'b']), static fn (int $k): bool => $k !== 0];
        yield  [[], Collection\Vector::fromArray(['a', 'b']), static fn (int $k): bool => false];
        yield  [[], Collection\Vector::fromArray([]), static fn (int $k): bool => false];
        yield  [[], ['a', 'b'], static fn (int $_) => false];
        yield  [['a', 'b'], ['a', 'b'], static fn (int $_): bool => true];
    }
}
