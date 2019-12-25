<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Iter;

/**
 * @covers \Psl\Arr\first
 */
class FirstTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFirst($expected, array $array): void
    {
        static::assertSame($expected, Arr\first($array));
    }

    public function provideData(): array
    {
        return [
            [
                null,
                [],
            ],

            [
                0,
                Iter\to_array(Iter\range(0, 10)),
            ],

            [
                null,
                [null],
            ],
        ];
    }
}
