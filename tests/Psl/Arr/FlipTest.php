<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;

class FlipTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFlip(array $expected, $actual): void
    {
        self::assertSame($expected, Arr\flip($actual));
    }

    public function provideData(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 'c' => 'd'],
                ['b' => 'a', 'c' => 'b', 'd' => 'c'],
            ],

            [
                [1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7, 9 => 8, 10 => 9],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ],
        ];
    }
}
