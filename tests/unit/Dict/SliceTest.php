<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class SliceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSlice(array $expected, array $array, int $n, null|int $l = null): void
    {
        $result = Dict\slice($array, $n, $l);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [['c' => 3, 'd' => 4], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [['b' => 2, 'c' => 3], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 1, 2];
    }
}
