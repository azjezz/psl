<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

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
        $chunks = Iter\chunk($iterable, $size);

        static::assertSame($expected, Iter\to_array($chunks));
    }

    public function provideData(): iterable
    {
        yield [[[1], [2], [3]], Iter\range(1, 3), 1];
        yield [[], [], 4];
        yield [[[1, 2, 3], [4, 5, 6], [7, 8, 9]], Iter\range(1, 9), 3];
        yield [[[1, 3, 5], [7, 9]], Iter\range(1, 9, 2), 3];
        yield [[[1, 3], [5, 7], [9]], Iter\range(1, 9, 2), 2];
    }
}
