<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;

final class MaxvaTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMaxva($expected, $first, $second, ...$rest): void
    {
        static::assertSame($expected, Math\maxva($first, $second, ...$rest));
    }

    public function provideData(): array
    {
        return [
            [
                10,
                10,
                5,
                ...Iter\range(0, 9, 2)
            ],

            [
                18,
                18,
                15,
                ...Iter\to_array(Iter\range(0, 10)),
                15
            ],

            [
                64,
                19,
                15,
                ...Iter\range(0, 45, 5),
                52,
                64
            ]
        ];
    }
}
