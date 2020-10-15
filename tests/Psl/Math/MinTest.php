<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;

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
                [...Iter\range(0, 10, 2)]
            ],

            [
                4,
                [...Iter\range(5, 10), 4]
            ],

            [
                null,
                []
            ]
        ];
    }
}
