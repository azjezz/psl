<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

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
        $chunks = Iter\chunk_with_keys($iterable, $size);

        static::assertSame($expected, Iter\to_array($chunks));
    }

    public function provideData(): iterable
    {
        yield [[[0 => 1], [1 => 2], [2 => 3]], Iter\range(1, 3), 1];
        yield [[], [], 4];
        yield [[[0 => 1, 1 => 2, 2 => 3], [3 => 4, 4 => 5, 5 => 6], [6 => 7, 7 => 8, 8 => 9]], Iter\range(1, 9), 3];
        yield [[[0 => 1, 1 => 3, 2 => 5], [3 => 7, 4 => 9]], Iter\range(1, 9, 2), 3];
        yield [[[0 => 1, 1 => 3], [2 => 5, 3 => 7], [4 => 9]], Iter\range(1, 9, 2), 2];
    }
}
