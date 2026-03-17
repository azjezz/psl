<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class Atan2Test extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testAtan2(float $expected, float $y, float $x): void
    {
        static::assertFloatEquals($expected, Math\atan2($y, $x));
    }

    public static function provideData(): array
    {
        return [
            [
                0.785_398_163_397_448_3,
                1.0,
                1.0,
            ],
            [
                0.896_055_384_571_343_9,
                1.0,
                0.8,
            ],
            [
                0.0,
                0.0,
                0.0,
            ],
            [
                0.785_398_163_397_448_3,
                0.4,
                0.4,
            ],
            [
                -2.260_001_062_633_476,
                -0.5,
                -0.412,
            ],
        ];
    }
}
