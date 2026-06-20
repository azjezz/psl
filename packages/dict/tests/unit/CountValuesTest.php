<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Vec;

final class CountValuesTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCountValues(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Dict\count_values::<string>($iterable));
    }

    public static function provideData(): array
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
                Collection\Vector::<string>::fromArray(['foo', 'bar', 'baz', 'foo', 'baz', 'baz', 'baz', 'baz']),
            ],
            [
                ['foo' => 2, 'bar' => 1, 'baz' => 4],
                Vec\concat::<string>(['foo', 'bar', 'baz'], ['foo'], ['baz'], ['baz', 'baz']),
            ],
        ];
    }
}
