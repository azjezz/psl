<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class TanTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testTan(float $expected, float $number, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        static::assertFloatEquals($expected, Math\tan($number), $epsilon);
    }

    public function provideData(): array
    {
        return [
            [
                -3.380515006246586,
                5.0,
                0.00000000000001,
            ],
            [
                -11.384870654242922,
                4.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.4227932187381618,
                0.4,
            ],
            [
                -0.22027720034589682,
                -6.5,
            ],
        ];
    }
}
