<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class Atan2Test extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testAtan2(float $expected, float $y, float $x): void
    {
        static::assertFloatEquals($expected, Math\atan2($y, $x));
    }

    public function provideData(): array
    {
        return [
            [
                0.7853981633974483,
                1.0,
                1.0,
            ],
            [
                0.8960553845713439,
                1.0,
                0.8,
            ],
            [
                0.0,
                0.0,
                0.0,
            ],
            [
                0.7853981633974483,
                0.4,
                0.4,
            ],
            [
                -2.260001062633476,
                -0.5,
                -0.412,
            ],
        ];
    }
}
