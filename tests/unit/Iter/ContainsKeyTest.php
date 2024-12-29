<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class ContainsKeyTest extends TestCase
{
    /**
     * @template Tk
     * @template Tv
     *
     * @param iterable<Tk, Tv> $iterable
     * @param Tk $key
     *
     * @dataProvider provideData
     */
    public function testContainsKey(bool $expected, iterable $iterable, $key): void
    {
        static::assertSame($expected, Iter\contains_key($iterable, $key));
    }

    public function provideData(): iterable
    {
        yield [false, [], 0];
        yield [false, [], 1];
        yield [true, [1, 2], 0];
        yield [true, [1, 2], 1];
        yield [false, [1, 2], 2];
        yield [true, ['hello' => 'world'], 'hello'];
        yield [false, ['hello' => 'world'], 'hellO'];
        yield [false, ['hello' => 'world'], 'world'];
        yield [false, [null => null], null];
        yield [true, new Collection\Vector([1, 2]), 0];
        yield [true, new Collection\Vector([1, 2]), 1];
        yield [false, new Collection\Vector([1, 2]), 2];
        yield [true, (static fn() => yield 'foo' => 'bar')(), 'foo'];
        yield [false, (static fn() => yield 'foo' => 'bar')(), 'bar'];
    }
}
