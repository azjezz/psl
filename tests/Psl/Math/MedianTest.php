<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Iter;
use Psl\Math;

final class MedianTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMedian($expected, array $numbers): void
    {
        static::assertSame($expected, Math\median($numbers));
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
                6.5,
                [
                    18,
                    15,
                    ...Iter\range(0, 10),
                    15,
                ],
            ],

            [
                22.5,
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
