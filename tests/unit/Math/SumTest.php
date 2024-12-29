<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class SumTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSum(int $expected, array $numbers): void
    {
        static::assertSame($expected, Math\sum($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                60,
                [
                    10,
                    5,
                    ...Vec\range(0, 9),
                ],
            ],
            [
                103,
                [
                    18,
                    15,
                    ...Vec\range(0, 10),
                    15,
                ],
            ],
            [
                534,
                [
                    178,
                    15,
                    ...Vec\range(0, 45, 5),
                    52,
                    64,
                ],
            ],
        ];
    }
}
