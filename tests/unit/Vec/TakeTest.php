<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class TakeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTake(array $expected, array $array, int $n): void
    {
        $result = Vec\take($array, $n);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [[1, 2], [1, 2, 3, 4], 2];
        yield [[1, 2], [1, 2], 3];
        yield [[], [1, 2], 0];
    }
}
