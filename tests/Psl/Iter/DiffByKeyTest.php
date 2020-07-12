<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class DiffByKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDiffByKey(array $expected, iterable $first, iterable $second, iterable ...$rest): void
    {
        $result = Iter\diff_by_key($first, $second, ...$rest);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[], [], [], []];
        yield [[], [], [1, 2, 3]];
        yield [[], [], [1, 2, 3], []];
        yield [[], [], [1, 2, 3], [], [4, 5]];

        yield [[1, 2], [1, 2], [], []];
        yield [[6 => 7, 7 => 8], Iter\range(1, 8), Iter\range(1, 6), []];
        yield [[7 => 8], Iter\range(1, 8), Iter\range(1, 6), [6 => 7]];
    }
}
