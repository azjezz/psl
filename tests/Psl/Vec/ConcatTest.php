<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ConcatTest extends TestCase
{
    /**
     * @psalm-template T
     *
     * @psalm-param list<T>     $expected
     * @psalm-param list<T>     $first
     * @psalm-param iterable<T> ...$rest
     *
     * @psalm-return list<T>
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
