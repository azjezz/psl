<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class SinTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testSin(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\sin($number));
    }

    public static function provideData(): array
    {
        return [
            [
                -0.958_924_274_663_138_5,
                5.0,
            ],
            [
                -0.996_164_608_835_840_7,
                4.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.389_418_342_308_650_5,
                0.4,
            ],
            [
                -0.215_119_988_087_815_52,
                -6.5,
            ],
        ];
    }
}
