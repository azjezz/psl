<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;

class CountValuesTest extends TestCase
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

    public function testCountThrowsForNonArrayKeyValues(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected all values to be of type array-key, value of type (object) provided.');

        Arr\count_values([
            new Collection\Map([]),
        ]);
    }
}
