<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ConcatTest extends TestCase
{
    /**
     * @template T
     *
     * @param list<T> $expected
     * @param list<T> $first
     * @param iterable<T> ...$rest
     *
     * @return list<T>
     *
     * @dataProvider provideData
     */
    public function testConcat(array $expected, array $first, iterable ...$rest): void
    {
        static::assertSame($expected, Vec\concat($first, ...$rest));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                ['foo' => 'a'],
                ['bar' => 'b'],
                ['baz' => 'c'],
            ],
            [
                ['foo', 'bar', 'baz', 'qux'],
                ['foo'],
                ['bar'],
                ['baz', 'qux'],
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
        ];
    }
}
