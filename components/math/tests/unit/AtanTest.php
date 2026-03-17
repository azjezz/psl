<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AtanTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testAtan(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\atan($number));
    }

    public static function provideData(): array
    {
        return [
            [
                0.785_398_163_397_448_3,
                1.0,
            ],
            [
                0.674_740_942_223_552_7,
                0.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.380_506_377_112_364_9,
                0.4,
            ],
            [
                -0.463_647_609_000_806_1,
                -0.5,
            ],
        ];
    }
}
