<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class RoundTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testRound(float $expected, float $number, int $precision = 0): void
    {
        static::assertSame($expected, Math\round($number, $precision));
    }

    public static function provideData(): array
    {
        return [
            [
                5.46,
                5.456_63,
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
                0.424_2,
                0,
            ],
            [
                0.5,
                0.463_4,
                1,
            ],
            [
                -6.577_78,
                -6.577_777_777_7,
                5,
            ],
        ];
    }
}
