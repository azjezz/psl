<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

class AbsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAbs($expected, $number): void
    {
        self::assertSame($expected, Math\abs($number));
    }

    public function provideData(): array
    {
        return  [
            [
                5,
                5
            ],

            [
                5,
                -5
            ],

            [
                5.5,
                -5.5
            ],

            [
                10.5,
                10.5
            ]
        ];
    }
}
