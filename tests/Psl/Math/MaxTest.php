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
    public function testMax($expected, iterable $numbers): void
    {
        self::assertSame($expected, Math\max($numbers));
    }

    public function provideData(): array
    {
        return [
            [
                10,
                Iter\range(0, 10, 2)
            ],

            [
                15,
                [...Iter\to_array(Iter\range(0, 10)), 15]
            ],

            [
                null,
                []
            ]
        ];
    }
}
