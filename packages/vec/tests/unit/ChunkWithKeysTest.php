<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ChunkWithKeysTest extends TestCase
{
    /**
     * @param list<array<mixed, mixed>> $expected
     * @param iterable<mixed, mixed> $iterable
     */
    #[DataProvider('provideData')]
    public function testChunkWithKeys(array $expected, iterable $iterable, int $size): void
    {
        static::assertSame($expected, Vec\chunk_with_keys::<int, int>($iterable, $size));
    }

    public static function provideData(): iterable
    {
        yield [[[0 => 1], [1 => 2], [2 => 3]], Vec\range::<int>(1, 3), 1];
        yield [[], [], 4];
        yield [[[0 => 1, 1 => 2, 2 => 3], [3 => 4, 4 => 5, 5 => 6], [6 => 7, 7 => 8, 8 => 9]], Vec\range::<int>(1, 9), 3];
        yield [[[0 => 1, 1 => 3, 2 => 5], [3 => 7, 4 => 9]], Vec\range::<int>(1, 9, 2), 3];
        yield [[[0 => 1, 1 => 3], [2 => 5, 3 => 7], [4 => 9]], Vec\range::<int>(1, 9, 2), 2];
    }
}
