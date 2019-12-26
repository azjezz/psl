<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

class MaxByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMaxBy($expected, iterable $values, callable $fun): void
    {
        self::assertSame($expected, Math\max_by($values, $fun));
    }

    public function provideData(): array
    {
        return [
            [
                'bazqux',
                ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
                fn ($value) => Str\length($value)
            ],

            [
                ['foo', 'bar', 'baz'],
                [
                    ['foo'],
                    ['foo', 'bar'],
                    ['foo', 'bar', 'baz']
                ],
                fn ($arr) => Iter\count($arr)
            ],

            [
                9,
                Iter\range(0, 9),
                fn ($i) => $i
            ],

            [
                null,
                [],
                fn ($i) => $i
            ]
        ];
    }
}
