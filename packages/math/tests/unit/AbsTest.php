<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AbsTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testAbs(int|float $expected, int|float $number): void
    {
        static::assertSame($expected, Math\abs($number));
    }

    public static function provideData(): array
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
