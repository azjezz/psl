<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MaxvaTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMaxva(int $expected, int $first, int $second, int ...$rest): void
    {
        static::assertSame($expected, Math\maxva($first, $second, ...$rest));
    }

    public static function provideData(): array
    {
        return [
            [
                10,
                10,
                5,
                ...Vec\range(0, 9, 2),
            ],
            [
                18,
                18,
                15,
                ...Vec\range(0, 10),
                15,
            ],
            [
                64,
                19,
                15,
                ...Vec\range(0, 45, 5),
                52,
                64,
            ],
        ];
    }
}
