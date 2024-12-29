<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Vec;

final class MinvaTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMainva($expected, $first, $second, ...$rest): void
    {
        static::assertSame($expected, Math\minva($first, $second, ...$rest));
    }

    public function provideData(): array
    {
        return [
            [
                5,
                10,
                5,
                ...Vec\range(7, 9, 2),
            ],
            [
                4,
                18,
                15,
                ...Vec\range(4, 10),
                15,
            ],
            [
                15,
                19,
                15,
                ...Vec\range(40, 45, 5),
                52,
                64,
            ],
        ];
    }
}
