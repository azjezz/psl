<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class DropTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDrop(array $expected, array $array, int $n): void
    {
        $result = Dict\drop($array, $n);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], 0];
        yield [['c' => 3, 'd' => 4], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [[3 => 4, 4 => 5], [1, 2, 3, 4, 5], 3];
        yield [[2 => 3, 3 => 4, 4 => 5], [1, 2, 3, 4, 5], 2];
        yield [[], [1, 2, 3, 4, 5], 5];
    }
}
