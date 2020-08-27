<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;

class SumTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSum(int $expected, array $numbers): void
    {
        self::assertSame($expected, Math\sum($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                60,
                [
                    10,
                    5,
                    ...Iter\range(0, 9),
                ],
            ],

            [
                103,
                [
                    18,
                    15,
                    ...Iter\to_array(Iter\range(0, 10)),
                    15,
                ],
            ],

            [
                534,
                [
                    178,
                    15,
                    ...Iter\range(0, 45, 5),
                    52,
                    64,
                ]
            ],
        ];
    }
}
