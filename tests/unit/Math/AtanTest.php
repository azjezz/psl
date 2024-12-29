<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AtanTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testAtan(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\atan($number));
    }

    public function provideData(): array
    {
        return [
            [
                0.7853981633974483,
                1.0,
            ],
            [
                0.6747409422235527,
                0.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.3805063771123649,
                0.4,
            ],
            [
                -0.4636476090008061,
                -0.5,
            ],
        ];
    }
}
