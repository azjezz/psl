<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

final class EqualTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEqualReturnsTheExpectedValue(bool $expected, array $array, array $other): void
    {
        static::assertSame($expected, Arr\equal($array, $other));
    }

    public function provideData(): array
    {
        return [
            [
                true,
                ['foo' => 'bar', 'baz' => 'qux'],
                ['baz' => 'qux', 'foo' => 'bar'],
            ],

            [
                false,
                ['foo' => 0, 'baz' => 1],
                ['foo' => '0', 'baz' => 1],
            ],

            [
                true,
                [],
                [],
            ],

            [
                false,
                [null],
                [],
            ],

            [
                false,
                [new Collection\Vector([])],
                [new Collection\Vector([])],
            ],
        ];
    }
}
