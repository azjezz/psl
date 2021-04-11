<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

final class PartitionTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPartition(array $expected, array $array, callable $predicate): void
    {
        static::assertSame($expected, Arr\partition($array, $predicate));
    }

    public function provideData(): array
    {
        return [
            [
                [['bar', 'baz'], ['foo', 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn (string $str) => Str\starts_with($str, 'b'),
            ],

            [
                [['bar', 'baz'], ['foo', 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn (string $str) => Str\starts_with($str, 'b'),
            ],

            [
                [[], []],
                [],
                static fn ($_) => false,
            ],

            [
                [[], ['foo', 'bar', 'baz', 'qux']],
                ['foo', 'bar', 'baz', 'qux'],
                static fn (string $str) => false,
            ],

            [
                [['foo', 'bar', 'baz', 'qux'], []],
                ['foo', 'bar', 'baz', 'qux'],
                static fn (string $str) => true,
            ],
        ];
    }
}
