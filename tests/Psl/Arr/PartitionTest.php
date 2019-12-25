<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Str;

class PartitionTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPartition(array $expected, iterable $iterable, callable $predicate): void
    {
        self::assertSame($expected, Arr\partition($iterable, $predicate));
    }

    public function provideData(): array
    {
        return [
            [
                [['bar', 'baz'], ['foo', 'qux']],
                new Collection\Vector(['foo', 'bar', 'baz', 'qux']),
                fn (string $str) => Str\starts_with($str, 'b'),
            ],

            [
                [['bar', 'baz'], ['foo', 'qux']],
                new Collection\Map(['foo', 'bar', 'baz', 'qux']),
                fn (string $str) => Str\starts_with($str, 'b'),
            ],

            [
                [[], []],
                [],
                fn ($_) => false,
            ],

            [
                [[], ['foo', 'bar', 'baz', 'qux']],
                new Collection\Map(['foo', 'bar', 'baz', 'qux']),
                fn (string $str) => false,
            ],

            [
                [['foo', 'bar', 'baz', 'qux'], []],
                new Collection\Map(['foo', 'bar', 'baz', 'qux']),
                fn (string $str) => true,
            ],
        ];
    }
}
