<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Str;

final class PartitionWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPartition(array $expected, array $array, Closure $predicate): void
    {
        static::assertSame($expected, Dict\partition_with_key($array, $predicate));
    }

    public function provideData(): array
    {
        return [
            [
                [[1 => 'bar', 2 => 'baz'], [0 => 'foo', 3 => 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $_k, string $str): bool => Str\starts_with($str, 'b'),
            ],
            [
                [[0 => 'foo', 3 => 'qux'], [1 => 'bar', 2 => 'baz']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $_k, string $str): bool => !Str\starts_with($str, 'b'),
            ],
            [
                [[], []],
                [],
                static fn(mixed $_k, mixed $_v): bool => false,
            ],
            [
                [[], ['foo', 'bar', 'baz', 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $_k, string $_str): bool => false,
            ],
            [
                [['foo', 'bar', 'baz', 'qux'], []],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $_k, string $_str): bool => true,
            ],
            [
                [[1 => 'bar', 2 => 'baz', 3 => 'qux'], ['foo']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $k, string $_str): bool => (bool) $k,
            ],
            [
                [['foo'], [1 => 'bar', 2 => 'baz', 3 => 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(int $k, string $_str): bool => !((bool) $k),
            ],
        ];
    }
}
