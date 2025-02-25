<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Str;

final class PartitionTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPartition(array $expected, array $array, Closure $predicate): void
    {
        static::assertSame($expected, Dict\partition($array, $predicate));
    }

    public function provideData(): array
    {
        return [
            [
                [[1 => 'bar', 2 => 'baz'], [0 => 'foo', 3 => 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(string $str): bool => Str\starts_with($str, 'b'),
            ],
            [
                [[0 => 'foo', 3 => 'qux'], [1 => 'bar', 2 => 'baz']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(string $str): bool => !Str\starts_with($str, 'b'),
            ],
            [
                [[], []],
                [],
                static fn(mixed $_): bool => false,
            ],
            [
                [[], ['foo', 'bar', 'baz', 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(string $_str): bool => false,
            ],
            [
                [['foo', 'bar', 'baz', 'qux'], []],
                ['foo', 'bar', 'baz', 'qux'],
                static fn(string $_str): bool => true,
            ],
        ];
    }
}
