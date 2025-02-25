<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

final class MaxByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMaxBy($expected, array $values, Closure $fun): void
    {
        static::assertSame($expected, Math\max_by($values, $fun));
    }

    public function provideData(): Generator
    {
        yield [
            'bazqux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            static fn(string $value): int => Str\length($value),
        ];

        yield [
            ['foo', 'bar', 'baz'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            static fn(array $arr): int => Iter\count($arr),
        ];

        yield [
            9,
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            static fn(int $i): int => $i,
        ];

        yield [
            null,
            [],
            static fn(int $i): int => $i,
        ];
    }
}
