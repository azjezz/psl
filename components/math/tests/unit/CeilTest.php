<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class CeilTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCiel(float $expected, float $number): void
    {
        static::assertSame($expected, Math\ceil($number));
    }

    public static function provideData(): array
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
