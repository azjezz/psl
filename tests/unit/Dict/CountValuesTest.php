<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Vec;

final class CountValuesTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCountValues(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Dict\count_values($iterable));
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
                ['foo' => 2, 'bar' => 1, 'baz' => 5],
                Collection\Vector::fromArray(['foo', 'bar', 'baz', 'foo', 'baz', 'baz', 'baz', 'baz']),
            ],
            [
                ['foo' => 2, 'bar' => 1, 'baz' => 4],
                Vec\concat(['foo', 'bar', 'baz'], ['foo'], ['baz'], ['baz', 'baz']),
            ],
        ];
    }
}
