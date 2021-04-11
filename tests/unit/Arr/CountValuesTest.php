<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class CountValuesTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCountValues(array $expected, array $array): void
    {
        static::assertSame($expected, Arr\count_values($array));
    }

    public function provideData(): array
    {
        return [
            [
                ['foo' => 1],
                ['foo'],
            ],
            [
                ['foo' => 2, 'bar' => 1, 'baz' => 5],
                ['foo', 'bar', 'baz', 'foo', 'baz', 'baz', 'baz', 'baz'],
            ],
            [
                ['foo' => 2, 'bar' => 1, 'baz' => 4],
                Arr\concat(
                    ['foo', 'bar', 'baz'],
                    ['foo'],
                    ['baz'],
                    ['baz', 'baz'],
                ),
            ],
        ];
    }
}
