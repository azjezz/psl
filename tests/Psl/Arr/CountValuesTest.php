<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;

/**
 * @covers \Psl\Arr\count_values
 */
class CountValuesTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCountValues(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Arr\count_values($iterable));
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
                    new Collection\Vector(['foo', 'bar', 'baz']),
                    new Collection\Map([1 => 'foo']),
                    (fn () => yield 'baz')(),
                    new Collection\Pair('baz', 'baz'),
                ),
            ],
            [
                [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1],
                Iter\range(1, 5),
            ],
            [
                [1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5],
                Arr\concat(
                    Iter\range(1, 5),
                    Iter\range(1, 5),
                    Iter\range(1, 5),
                    Iter\range(1, 5),
                    Iter\range(1, 5),
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
