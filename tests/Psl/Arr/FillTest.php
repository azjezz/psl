<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class FillTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFill(array $expected, $value, int $start, int $num): void
    {
        $array = Arr\fill($value, $start, $num);

        static::assertSame($expected, $array);

        for ($i = $start; $i < $start + $num; ++$i) {
            static::assertArrayHasKey($i, $array);
            static::assertSame($value, Arr\at($array, $i));
        }
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'a', 'a'],
                'a',
                0,
                3,
            ],

            [
                [],
                'a',
                0,
                0,
            ],

            [
                [5 => true, 6 => true, 7 => true],
                true,
                5,
                3,
            ],

            [
                [8 => null, 9 => null, 10 => null],
                null,
                8,
                3,
            ],
        ];
    }
}
