<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

final class MaxByTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMaxBy(null|string|int|array $expected, array $values, Closure $fun): void
    {
        static::assertSame($expected, Math\max_by($values, $fun));
    }

    public static function provideData(): Generator
    {
        yield [
            'bazqux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            Str\length(...),
        ];

        yield [
            ['foo', 'bar', 'baz'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            Iter\count(...),
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
