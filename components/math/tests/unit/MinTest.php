<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MinTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMin(null|int $expected, array $numbers): void
    {
        static::assertSame($expected, Math\min($numbers));
    }

    public static function provideData(): array
    {
        return [
            [
                0,
                [...Vec\range(0, 10, 2)],
            ],
            [
                4,
                [...Vec\range(5, 10), 4],
            ],
            [
                null,
                [],
            ],
        ];
    }
}
