<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

class DivTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDiv(int $expected, int $numerator, int $denominator): void
    {
        self::assertSame($expected, Math\div($numerator, $denominator));
    }

    public function provideData(): array
    {
        return[
            [
                2,
                5,
                2,
            ],

            [
                5,
                10,
                2
            ],

            [
                0,
                15,
                20
            ],

            [
                1,
                10,
                10
            ]
        ];
    }
}
