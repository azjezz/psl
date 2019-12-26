<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Iter;
use Psl\Math;

class MeanTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMean($expected, iterable $numbers): void
    {
        self::assertSame($expected, Math\mean($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                5.0,
                [
                    10,
                    5,
                    ...Iter\range(0, 9, 2),
                ],
            ],

            [
                7.357142857142858,
                [
                    18,
                    15,
                    ...Iter\to_array(Iter\range(0, 10)),
                    15,
                ],
            ],

            [
                26.785714285714285,
                [
                    19,
                    15,
                    ...Iter\range(0, 45, 5),
                    52,
                    64,
                ],
            ],

            [
                100.0,
                Arr\fill(100, 0, 100)
            ],


            [
                null,
                []
            ]
        ];
    }
}
