<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class IdxTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIdx($expected, array $array, $key, $default): void
    {
        static::assertSame($expected, Arr\idx($array, $key, $default));
    }

    public function provideData(): array
    {
        return [
            [6, [], 5, 6],
            [null, ['foo' => null], 'foo', 'bar'],
            [1, [1], 0, 2],
        ];
    }
}
