<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterKeys(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Iter\filter_keys($iterable, $predicate);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [[1 => 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], static fn () => false];
        yield  [['a', 'b'], ['a', 'b'], static fn (int $_): bool => true];
        yield  [['a'], ['a', 'b'], static fn (int $k): bool => 1 !== $k];

        yield  [[1 => 'b'], Iter\Iterator::create(['a', 'b'])];
        yield  [[], Iter\Iterator::create(['a', 'b']), static fn () => false];
        yield  [['a', 'b'], Iter\Iterator::create(['a', 'b']), static fn (int $_): bool => true];
        yield  [['a'], Iter\Iterator::create(['a', 'b']), static fn (int $k): bool => 1 !== $k];

        yield  [[1 => 'b'], (static function () {
            yield 'a';
            yield 'b';
        })()];
        yield  [[], (static function () {
            yield 'a';
            yield 'b';
        })(), static fn () => false];
        yield  [['a', 'b'], (static function () {
            yield 'a';
            yield 'b';
        })(), static fn (int $_): bool => true];
        yield  [['a'], (static function () {
            yield 'a';
            yield 'b';
        })(), static fn (int $k): bool => 1 !== $k];
    }
}
