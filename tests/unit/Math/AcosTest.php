<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AcosTest extends TestCase
{
    use FloatAsserts;

    /**
     * @dataProvider provideData
     */
    public function testAcos(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\acos($number));
    }

    public function provideData(): array
    {
        return [
            [
                0.0,
                1.0,
            ],
            [
                1.2661036727794992,
                0.3,
            ],
            [
                1.0471975511965979,
                0.5,
            ],
        ];
    }
}
