<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class RoundTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testRound(float $expected, float $number, int $precision = 0): void
    {
        static::assertSame($expected, Math\round($number, $precision));
    }

    public function provideData(): array
    {
        return [
            [
                5.46,
                5.45663,
                2,
            ],
            [
                4.8,
                4.811,
                1,
            ],
            [
                5.0,
                5.42,
                0,
            ],
            [
                5.0,
                4.8,
                0,
            ],
            [
                0.0,
                0.4242,
                0,
            ],
            [
                0.5,
                0.4634,
                1,
            ],
            [
                -6.57778,
                -6.5777777777,
                5,
            ],
        ];
    }
}
