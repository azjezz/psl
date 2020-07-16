<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterKeys(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Iter\filter_keys($iterable, $predicate);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [[1 => 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], fn () => false];
        yield  [['a', 'b'], ['a', 'b'], fn (int $_): bool => true];
        yield  [['a'], ['a', 'b'], fn (int $k): bool => 1 !== $k];

        yield  [[1 => 'b'], new Iter\Iterator(['a', 'b'])];
        yield  [[], new Iter\Iterator(['a', 'b']), fn () => false];
        yield  [['a', 'b'], new Iter\Iterator(['a', 'b']), fn (int $_): bool => true];
        yield  [['a'], new Iter\Iterator(['a', 'b']), fn (int $k): bool => 1 !== $k];

        yield  [[1 => 'b'], (static function () {
            yield 'a';
            yield 'b';
        })()];
        yield  [[], (static function () {
            yield 'a';
            yield 'b';
        })(), fn () => false];
        yield  [['a', 'b'], (static function () {
            yield 'a';
            yield 'b';
        })(), fn (int $_): bool => true];
        yield  [['a'], (static function () {
            yield 'a';
            yield 'b';
        })(), fn (int $k): bool => 1 !== $k];
    }
}
