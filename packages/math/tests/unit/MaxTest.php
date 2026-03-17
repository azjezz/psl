<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class MaxTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMax(null|int $expected, array $numbers): void
    {
        static::assertSame($expected, Math\max($numbers));
    }

    public static function provideData(): array
    {
        return [
            [
                10,
                [0, 2, 4, 6, 8, 10],
            ],
            [
                15,
                [0, 2, 4, 6, 8, 10, 15],
            ],
            [
                null,
                [],
            ],
        ];
    }
}
