<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

final class UniqueByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testUniqueBy(array $expected, array $array, callable $scalar_fun): void
    {
        static::assertSame($expected, Arr\unique_by($array, $scalar_fun));
    }

    public function provideData(): array
    {
        return [
            [
                [0 => 'a', 4 => 'saif'],
                ['a', 'b', 'c', 'd', 'saif', 'jack'],
                static fn (string $value): int => Str\length($value),
            ],

            [
                [0 => 'foo', 2 => 'bar', 4 => '@baz'],
                ['foo', '@foo', 'bar', '@bar', '@baz'],
                static fn (string $value): string => Str\replace($value, '@', ''),
            ],
        ];
    }
}
