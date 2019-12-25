<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

/**
 * @covers \Psl\Arr\last_key
 */
class LastKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLastKey($expected, array $array): void
    {
        static::assertSame($expected, Arr\last_key($array));
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
                2,
                [1 => 'foo', 2 => 'bar'],
            ],

            [
                'baz',
                ['bar' => 'baz', 'baz' => 'qux'],
            ],
        ];
    }
}
