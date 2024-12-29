<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

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
                    ...Vec\range(0, 9, 2),
                ],
            ],
            [
                6.5,
                [
                    18,
                    15,
                    ...Vec\range(0, 10),
                    15,
                ],
            ],
            [
                22.5,
                [
                    19,
                    15,
                    ...Vec\range(0, 45, 5),
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
