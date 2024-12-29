<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class CosTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testCos(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\cos($number));
    }

    public function provideData(): array
    {
        return [
            [
                0.5403023058681398,
                1.0,
            ],
            [
                1.0,
                0.0,
            ],
            [
                0.10291095660695612,
                45.45,
            ],
            [
                0.28366218546322625,
                -5,
            ],
            [
                -0.9983206000589924,
                -15.65,
            ],
        ];
    }
}
