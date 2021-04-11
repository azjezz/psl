<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

final class MinByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMinBy($expected, array $values, callable $fun): void
    {
        static::assertSame($expected, Math\min_by($values, $fun));
    }

    public function provideData(): Generator
    {
        yield [
            'qux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            static fn ($value) => Str\length($value),
        ];

        yield [
            ['foo'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz']
            ],
            static fn ($arr) => Iter\count($arr),
        ];

        yield [
            0,
            [...Iter\range(0, 9)],
            static fn ($i) => $i,
        ];

        yield [
            null,
            [],
            static fn ($i) => $i,
        ];
    }
}
