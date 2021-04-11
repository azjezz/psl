<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class TanTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testTan(float $expected, float $number): void
    {
        static::assertSame($expected, Math\tan($number));
    }

    public function provideData(): array
    {
        return [
            [
                -3.380515006246586,
                5.0
            ],

            [
                -11.384870654242922,
                4.8
            ],

            [
                0.0,
                0.0
            ],

            [
                0.4227932187381618,
                0.4
            ],

            [
                -0.22027720034589682,
                -6.5
            ]
        ];
    }
}
