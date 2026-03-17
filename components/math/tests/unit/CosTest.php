<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class CosTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testCos(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\cos($number));
    }

    public static function provideData(): array
    {
        return [
            [
                0.540_302_305_868_139_8,
                1.0,
            ],
            [
                1.0,
                0.0,
            ],
            [
                0.102_910_956_606_956_12,
                45.45,
            ],
            [
                0.283_662_185_463_226_25,
                -5,
            ],
            [
                -0.998_320_600_058_992_4,
                -15.65,
            ],
        ];
    }
}
