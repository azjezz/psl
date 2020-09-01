<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class FilterTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilter(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Iter\filter($iterable, $predicate);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [['a', 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], fn () => false];
        yield  [['a', 'b'], ['a', 'b'], fn (string $_): bool => true];
        yield  [['a'], ['a', 'b'], fn (string $v): bool => 'b' !== $v];

        yield  [['a', 'b'], Iter\Iterator::create(['a', 'b'])];
        yield  [[], Iter\Iterator::create(['a', 'b']), fn () => false];
        yield  [['a', 'b'], Iter\Iterator::create(['a', 'b']), fn (string $_): bool => true];
        yield  [['a'], Iter\Iterator::create(['a', 'b']), fn (string $v): bool => 'b' !== $v];

        yield  [['a', 'b'], (static function () {
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
        })(), fn (string $_): bool => true];
        yield  [['a'], (static function () {
            yield 'a';
            yield 'b';
        })(), fn (string $v): bool => 'b' !== $v];
    }
}
