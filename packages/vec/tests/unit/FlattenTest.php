<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class FlattenTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFlatten(array $expected, array $iterables): void
    {
        static::assertSame($expected, Vec\flatten($iterables));
    }

    public static function provideData(): iterable
    {
        yield 'basic' => [[1, 2, 3, 4, 5], [[1, 2], [3, 4], [5]]];
        yield 'with empty inner' => [['a', 'b', 'c'], [['a', 'b'], [], ['c']]];
        yield 'empty outer' => [[], []];
        yield 'all empty inner' => [[], [[], [], []]];
        yield 'single inner' => [[1, 2, 3], [[1, 2, 3]]];
        yield 'nested arrays stay nested' => [[[1], [2]], [[[1]], [[2]]]];
        yield 'strings' => [['a', 'b', 'c', 'd'], [['a', 'b'], ['c', 'd']]];
        yield 'mixed types' => [[1, 'a', 2, 'b'], [[1, 'a'], [2, 'b']]];
        yield 'preserves order' => [[3, 1, 4, 1, 5], [[3, 1], [4], [1, 5]]];
    }

    public function testFlattenWithIterables(): void
    {
        $generator = static function (): iterable {
            yield [1, 2];
            yield [3, 4];
        };

        static::assertSame([1, 2, 3, 4], Vec\flatten($generator()));
    }

    public function testFlattenWithInnerGenerators(): void
    {
        $inner = static function (int $start, int $end): iterable {
            for ($i = $start; $i <= $end; $i++) {
                yield $i;
            }
        };

        static::assertSame([1, 2, 3, 4, 5, 6], Vec\flatten([$inner(1, 3), $inner(4, 6)]));
    }
}
