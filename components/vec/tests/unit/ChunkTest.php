<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ChunkTest extends TestCase
{
    /**
     * @template T
     * @param list<list<T>> $expected
     * @param iterable<T> $iterable
     */
    #[DataProvider('provideData')]
    public function testChunk(array $expected, iterable $iterable, int $size): void
    {
        static::assertSame($expected, Vec\chunk($iterable, $size));
    }

    public static function provideData(): iterable
    {
        yield [[[1], [2], [3]], Vec\range(1, 3), 1];
        yield [[], [], 4];
        yield [[[1, 2, 3], [4, 5, 6], [7, 8, 9]], Vec\range(1, 9), 3];
        yield [[[1, 3, 5], [7, 9]], Vec\range(1, 9, 2), 3];
        yield [[[1, 3], [5, 7], [9]], Vec\range(1, 9, 2), 2];
    }

    public function testChunkWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3, 4, 5]);

        static::assertSame([[1, 2], [3, 4], [5]], Vec\chunk($iterator, 2));
    }

    public function testChunkWithNonArrayIterableExactDivision(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3, 4, 5, 6]);

        static::assertSame([[1, 2, 3], [4, 5, 6]], Vec\chunk($iterator, 3));
    }
}
