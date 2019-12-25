<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class FirstKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFirstKey($expected, array $array): void
    {
        static::assertSame($expected, Arr\first_key($array));
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
                [1],
            ],

            [
                1,
                [1 => 'foo'],
            ],

            [
                'bar',
                ['bar' => 'baz', 'baz' => 'qux'],
            ],
        ];
    }
}
