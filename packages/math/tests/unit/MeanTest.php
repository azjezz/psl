<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MeanTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMean(null|float $expected, array $numbers): void
    {
        static::assertSame($expected, Math\mean($numbers));
    }

    public static function provideData(): array
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
                7.357_142_857_142_858,
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
                26.785_714_285_714_285,
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
