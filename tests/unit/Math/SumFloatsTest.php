<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class SumFloatsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSumFloats(float $expected, array $numbers): void
    {
        static::assertSame($expected, Math\sum_floats($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                116.70000000000005,
                [
                    10.9,
                    5,
                    ...Vec\range(0, 9.8798, 0.48),
                ],
            ],
            [
                103.0,
                [
                    18,
                    15,
                    ...Vec\range(0, 10),
                    15,
                ],
            ],
            [
                323.54,
                [
                    19.5,
                    15.8,
                    ...Vec\range(0.5, 45, 5.98),
                    52.8,
                    64,
                ],
            ],
        ];
    }
}
