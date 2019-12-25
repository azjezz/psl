<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class ContainsKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testContainsKey(bool $expected, array $array, $key): void
    {
        static::assertSame($expected, Arr\contains_key($array, $key));
    }

    public function provideData(): array
    {
        return [
            [false, [], 5],
            [true, ['foo' => null], 'foo'],
            [true, [1], 0],
        ];
    }
}
