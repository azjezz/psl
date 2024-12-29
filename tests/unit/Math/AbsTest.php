<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AbsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAbs($expected, $number): void
    {
        static::assertSame($expected, Math\abs($number));
    }

    public function provideData(): array
    {
        return [
            [
                5,
                5,
            ],
            [
                5,
                -5,
            ],
            [
                5.5,
                -5.5,
            ],
            [
                10.5,
                10.5,
            ],
        ];
    }
}
