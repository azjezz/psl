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
use Psl\Vec;

final class MinByTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMinBy(null|int|string|array $expected, array $values, Closure $fun): void
    {
        static::assertSame($expected, Math\min_by($values, $fun));
    }

    public static function provideData(): Generator
    {
        yield [
            'qux',
            ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
            Str\length(...),
        ];

        yield [
            ['foo'],
            [
                ['foo'],
                ['foo', 'bar'],
                ['foo', 'bar', 'baz'],
            ],
            Iter\count(...),
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
