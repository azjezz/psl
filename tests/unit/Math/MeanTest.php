<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MeanTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMean($expected, array $numbers): void
    {
        static::assertSame($expected, Math\mean($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                5.0,
                [
                    10,
                    5,
                    0,
                    2,
                    4,
                    6,
                    8,
                ],
            ],
            [
                7.357142857142858,
                [
                    18,
                    15,
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                    15,
                ],
            ],
            [
                26.785714285714285,
                [
                    19,
                    15,
                    0,
                    5,
                    10,
                    15,
                    20,
                    25,
                    30,
                    35,
                    40,
                    45,
                    52,
                    64,
                ],
            ],
            [
                100.0,
                Vec\fill(100, 100),
            ],
            [
                null,
                [],
            ],
        ];
    }
}
