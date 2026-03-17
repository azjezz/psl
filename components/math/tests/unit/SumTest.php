<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class SumTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSum(int $expected, array $numbers): void
    {
        static::assertSame($expected, Math\sum($numbers));
    }

    public static function provideData(): array
    {
        return [
            [
                60,
                [
                    10,
                    5,
                    ...Vec\range(0, 9),
                ],
            ],
            [
                103,
                [
                    18,
                    15,
                    ...Vec\range(0, 10),
                    15,
                ],
            ],
            [
                534,
                [
                    178,
                    15,
                    ...Vec\range(0, 45, 5),
                    52,
                    64,
                ],
            ],
        ];
    }
}
