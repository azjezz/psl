<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Vec;

final class DiffByKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDiffByKey(array $expected, iterable $first, iterable $second, iterable ...$rest): void
    {
        static::assertSame($expected, Dict\diff_by_key($first, $second, ...$rest));
    }

    public function provideData(): iterable
    {
        yield [[], [], [], []];
        yield [[], [], [1, 2, 3]];
        yield [[], [], [1, 2, 3], []];
        yield [[], [], [1, 2, 3], [], [4, 5]];

        yield [[1, 2], [1, 2], [], []];
        yield [[1, 2], [1, 2], ['foo' => 2], []];
        yield [[1, 2], [1, 2], [], ['baz' => 1]];
        yield [[6 => 7, 7 => 8], Vec\range(1, 8), Vec\range(1, 6), []];
        yield [[7 => 8], Vec\range(1, 8), Vec\range(1, 6), [6 => 7]];
    }
}
