<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class DropTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDrop(array $expected, array $array, int $n): void
    {
        $result = Arr\drop($array, $n);

        self::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], 0];
        yield [[3 => 4, 4 => 5], [1, 2, 3, 4, 5], 3];
        yield [[2 => 3, 3 => 4, 4 => 5], [1, 2, 3, 4, 5], 2];
        yield [[], [1, 2, 3, 4, 5], 5];
    }
}
