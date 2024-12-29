<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MinTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMin($expected, array $numbers): void
    {
        static::assertSame($expected, Math\min($numbers));
    }

    public function provideData(): array
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
