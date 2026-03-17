<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class FloorTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFloor(float $expected, float $number): void
    {
        static::assertSame($expected, Math\floor($number));
    }

    public static function provideData(): array
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
