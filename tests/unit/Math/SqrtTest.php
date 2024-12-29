<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class SqrtTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSqrt(float $expected, float $number): void
    {
        static::assertSame($expected, Math\sqrt($number));
    }

    public function provideData(): array
    {
        return [
            [
                2.23606797749979,
                5.0,
            ],
            [
                2.1908902300206643,
                4.8,
            ],
            [
                0.6324555320336759,
                0.4,
            ],
            [
                2.5495097567963922,
                6.5,
            ],
            [
                1.4142135623730951,
                2,
            ],
            [
                1,
                1,
            ],
        ];
    }
}
