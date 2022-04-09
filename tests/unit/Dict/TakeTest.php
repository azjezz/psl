<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class TakeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTake(array $expected, array $array, int $n): void
    {
        $result = Dict\take($array, $n);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[-5, -4, -3], [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 3];
        yield [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [[1, 2], [1, 2], 3];
        yield [[], [1, 2], 0];
    }
}
