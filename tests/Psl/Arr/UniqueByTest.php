<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

class UniqueByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testUniqueBy(array $expected, iterable $iterable, callable $scalar_fun): void
    {
        static::assertSame($expected, Arr\unique_by($iterable, $scalar_fun));
    }

    public function provideData(): array
    {
        return [
            [
                [0 => 'a', 4 => 'saif'],
                ['a', 'b', 'c', 'd', 'saif', 'jack'],
                fn ($value) => Str\length($value),
            ],

            [
                [0 => 'foo', 2 => 'bar', 4 => '@baz'],
                ['foo', '@foo', 'bar', '@bar', '@baz'],
                fn ($value) => Str\replace($value, '@', ''),
            ],
        ];
    }
}
