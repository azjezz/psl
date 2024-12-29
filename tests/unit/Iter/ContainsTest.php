<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class ContainsTest extends TestCase
{
    /**
     * @template T
     *
     * @param iterable<T> $iterable
     * @param T $value
     *
     * @dataProvider provideData
     */
    public function testContainsKey(bool $expected, iterable $iterable, $value): void
    {
        static::assertSame($expected, Iter\contains($iterable, $value));
    }

    public function provideData(): iterable
    {
        yield [false, [], 0];
        yield [false, [], 1];
        yield [false, [], null];
        yield [false, [0], null];
        yield [true, [null], null];
        yield [false, [1, 2], 0];
        yield [true, [1, 2], 1];
        yield [true, [1, 2], 2];
        yield [false, ['hello' => 'world'], 'hello'];
        yield [true, ['hello' => 'world'], 'world'];
        yield [false, ['hello' => 'world'], 'worlD'];
        yield [true, [null => null], null];
        yield [false, new Collection\Vector([1, 2]), 0];
        yield [true, new Collection\Vector([1, 2]), 1];
        yield [true, new Collection\Vector([1, 2]), 2];
        yield [false, (static fn() => yield 'foo' => 'bar')(), 'foo'];
        yield [true, (static fn() => yield 'foo' => 'bar')(), 'bar'];
    }
}
