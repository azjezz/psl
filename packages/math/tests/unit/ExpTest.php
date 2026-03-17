<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class ExpTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testExp(float $expected, float $number): void
    {
        static::assertSame($expected, Math\exp($number));
    }

    public static function provideData(): array
    {
        return [
            [
                162_754.791_419_003_92,
                12.0,
            ],
            [
                298.867_400_967_060_3,
                5.7,
            ],
            [
                Math\INFINITY,
                1_000_000,
            ],
        ];
    }
}
