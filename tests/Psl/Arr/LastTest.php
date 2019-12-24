<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Iter;

/**
 * @covers \Psl\Arr\last
 */
class LastTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLast($expected, array $array): void
    {
        static::assertSame($expected, Arr\last($array));
    }

    public function provideData(): array
    {
        return [
            [
                null,
                [],
            ],

            [
                10,
                Iter\to_array(Iter\range(0, 10)),
            ],

            [
                null,
                [null],
            ],
        ];
    }
}
