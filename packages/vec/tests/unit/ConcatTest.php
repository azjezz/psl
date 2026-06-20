<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ConcatTest extends TestCase
{
    /**
     * @param list<mixed> $expected
     * @param list<mixed> $first
     * @param iterable<mixed> ...$rest
     * @return list<mixed>
     */
    #[DataProvider('provideData')]
    public function testConcat(array $expected, array $first, iterable ...$rest): void
    {
        static::assertSame($expected, Vec\concat::<string|int>($first, ...$rest));
    }

    public static function provideData(): array
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

    public function testConcatWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::<string, string>::create(['x' => 'a', 'y' => 'b']);

        static::assertSame(['c', 'a', 'b'], Vec\concat::<string>(['c'], $iterator));
    }
}
