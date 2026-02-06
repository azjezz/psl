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
                162_754.791_419_003_92,
                12.0,
            ],
            [
                298.867_400_967_060_3,
                5.7,
            ],
            [
                Math\INFINITY,
                1_000_000,
            ],
        ];
    }
}
