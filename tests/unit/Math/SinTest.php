<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class SinTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testSin(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\sin($number));
    }

    public function provideData(): array
    {
        return [
            [
                -0.9589242746631385,
                5.0,
            ],
            [
                -0.9961646088358407,
                4.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.3894183423086505,
                0.4,
            ],
            [
                -0.21511998808781552,
                -6.5,
            ],
        ];
    }
}
