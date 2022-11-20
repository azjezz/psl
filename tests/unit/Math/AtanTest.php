<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\OS;

final class AtanTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAtan(float $expected, float $number): void
    {
        if (OS\is_darwin()) {
            static::markTestSkipped('floating point problem on macos.');
        }

        static::assertSame($expected, Math\atan($number));
    }

    public function provideData(): array
    {
        return [
            [
                0.7853981633974483,
                1.0
            ],

            [
                0.6747409422235527,
                0.8
            ],

            [
                0.0,
                0.0
            ],

            [
                0.3805063771123649,
                0.4
            ],

            [
                -0.4636476090008061,
                -0.5
            ]
        ];
    }
}
