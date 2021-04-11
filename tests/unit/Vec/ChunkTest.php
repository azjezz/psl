<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ChunkTest extends TestCase
{
    /**
     * @template T
     *
     * @param list<list<T>> $expected
     * @param iterable<T> $iterable
     *
     * @dataProvider provideData
     */
    public function testChunk(array $expected, iterable $iterable, int $size): void
    {
        static::assertSame($expected, Vec\chunk($iterable, $size));
    }

    public function provideData(): iterable
    {
        yield [[[1], [2], [3]], Vec\range(1, 3), 1];
        yield [[], [], 4];
        yield [[[1, 2, 3], [4, 5, 6], [7, 8, 9]], Vec\range(1, 9), 3];
        yield [[[1, 3, 5], [7, 9]], Vec\range(1, 9, 2), 3];
        yield [[[1, 3], [5, 7], [9]], Vec\range(1, 9, 2), 2];
    }
}
