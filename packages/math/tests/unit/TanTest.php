<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class TanTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testTan(float $expected, float $number, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        static::assertFloatEquals($expected, Math\tan($number), $epsilon);
    }

    public static function provideData(): array
    {
        return [
            [
                -3.380_515_006_246_586,
                5.0,
                0.000_000_000_000_01,
            ],
            [
                -11.384_870_654_242_922,
                4.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.422_793_218_738_161_8,
                0.4,
            ],
            [
                -0.220_277_200_345_896_82,
                -6.5,
            ],
        ];
    }
}
