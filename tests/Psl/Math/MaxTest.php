<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;

class MaxTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMax($expected, array $numbers): void
    {
        self::assertSame($expected, Math\max($numbers));
    }

    public function provideData(): array
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
                []
            ]
        ];
    }
}
