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
     * @param list<list<mixed>> $expected
     * @param iterable<mixed> $iterable
     */
    #[DataProvider('provideData')]
    public function testChunk(array $expected, iterable $iterable, int $size): void
    {
        static::assertSame($expected, Vec\chunk::<int>($iterable, $size));
    }

    public static function provideData(): iterable
    {
        yield [[[1], [2], [3]], Vec\range::<int>(1, 3), 1];
        yield [[], [], 4];
        yield [[[1, 2, 3], [4, 5, 6], [7, 8, 9]], Vec\range::<int>(1, 9), 3];
        yield [[[1, 3, 5], [7, 9]], Vec\range::<int>(1, 9, 2), 3];
        yield [[[1, 3], [5, 7], [9]], Vec\range::<int>(1, 9, 2), 2];
    }

    public function testChunkWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::<int, int>::create([1, 2, 3, 4, 5]);

        static::assertSame([[1, 2], [3, 4], [5]], Vec\chunk::<int>($iterator, 2));
    }

    public function testChunkWithNonArrayIterableExactDivision(): void
    {
        $iterator = Iter\Iterator::<int, int>::create([1, 2, 3, 4, 5, 6]);

        static::assertSame([[1, 2, 3], [4, 5, 6]], Vec\chunk::<int>($iterator, 3));
    }
}
