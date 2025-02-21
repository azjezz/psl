<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;
use Psl\Vec;
use Closure;

final class MinByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMinBy($expected, array $values, Closure $fun): void
    {
        static::assertSame($expected, Math\min_by($values, $fun));
    }

    public function provideData(): Generator
    {
        yield [
            'qux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            static fn(string $value): int => Str\length($value),
        ];

        yield [
            ['foo'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            static fn(array $arr): int => Iter\count($arr),
        ];

        yield [
            0,
            [...Vec\range(0, 9)],
            static fn(int $i): int => $i,
        ];

        yield [
            null,
            [],
            static fn(int $i): int => $i,
        ];
    }
}
