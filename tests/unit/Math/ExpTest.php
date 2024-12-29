<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class ExpTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testExp(float $expected, float $number): void
    {
        static::assertSame($expected, Math\exp($number));
    }

    public function provideData(): array
    {
        return [
            [
                162754.79141900392,
                12.0,
            ],
            [
                298.8674009670603,
                5.7,
            ],
            [
                Math\INFINITY,
                1000000,
            ],
        ];
    }
}
