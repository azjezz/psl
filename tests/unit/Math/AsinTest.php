<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AsinTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testAsin(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\asin($number));
    }

    public function provideData(): array
    {
        return [
            [
                0.5235987755982989,
                0.5,
            ],
            [
                0.9272952180016123,
                0.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                0.41151684606748806,
                0.4,
            ],
        ];
    }
}
