<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;

class MinvaTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMainva($expected, $first, $second, ...$rest): void
    {
        self::assertSame($expected, Math\minva($first, $second, ...$rest));
    }

    public function provideData(): array
    {
        return [
            [
                5,
                10,
                5,
                ...Iter\range(7, 9, 2)
            ],

            [
                4,
                18,
                15,
                ...Iter\to_array(Iter\range(4, 10)),
                15
            ],

            [
                15,
                19,
                15,
                ...Iter\range(40, 45, 5),
                52,
                64
            ]
        ];
    }
}
