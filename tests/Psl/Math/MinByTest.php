<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

class MinByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMinBy($expected, array $values, callable $fun): void
    {
        self::assertSame($expected, Math\min_by($values, $fun));
    }

    public function provideData(): array
    {
        return [
            [
                'qux',
                ['foo', 'bar', 'baz', 'qux', 'foobar', 'bazqux'],
                fn ($value) => Str\length($value)
            ],

            [
                ['foo'],
                [
                    ['foo'],
                    ['foo', 'bar'],
                    ['foo', 'bar', 'baz']
                ],
                fn ($arr) => Iter\count($arr)
            ],

            [
                0,
                [...Iter\range(0, 9)],
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
