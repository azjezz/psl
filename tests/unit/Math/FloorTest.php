<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class FloorTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFloor(float $expected, float $number): void
    {
        static::assertSame($expected, Math\floor($number));
    }

    public function provideData(): array
    {
        return [
            [
                4,
                4.3,
            ],
            [
                9,
                9.9,
            ],
            [
                3,
                Math\PI,
            ],
            [
                -4,
                -Math\PI,
            ],
            [
                2,
                Math\E,
            ],
        ];
    }
}
