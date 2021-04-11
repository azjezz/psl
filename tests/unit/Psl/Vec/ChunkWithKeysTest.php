<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ChunkWithKeysTest extends TestCase
{
    /**
     * @template Tk
     * @template Tv
     *
     * @param list<array<Tk, Tv>> $expected
     * @param iterable<Tk, Tv> $iterable
     *
     * @dataProvider provideData
     */
    public function testChunkWithKeys(array $expected, iterable $iterable, int $size): void
    {
        static::assertSame($expected, Vec\chunk_with_keys($iterable, $size));
    }

    public function provideData(): iterable
    {
        yield [[[0 => 1], [1 => 2], [2 => 3]], Vec\range(1, 3), 1];
        yield [[], [], 4];
        yield [[[0 => 1, 1 => 2, 2 => 3], [3 => 4, 4 => 5, 5 => 6], [6 => 7, 7 => 8, 8 => 9]], Vec\range(1, 9), 3];
        yield [[[0 => 1, 1 => 3, 2 => 5], [3 => 7, 4 => 9]], Vec\range(1, 9, 2), 3];
        yield [[[0 => 1, 1 => 3], [2 => 5, 3 => 7], [4 => 9]], Vec\range(1, 9, 2), 2];
    }
}
