<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class CeilTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCiel(float $expected, float $number): void
    {
        static::assertSame($expected, Math\ceil($number));
    }

    public function provideData(): array
    {
        return [
            [
                5.0,
                5.0,
            ],
            [
                5.0,
                4.8,
            ],
            [
                0.0,
                0.0,
            ],
            [
                1.0,
                0.4,
            ],
            [
                -6.0,
                -6.5,
            ],
        ];
    }
}
