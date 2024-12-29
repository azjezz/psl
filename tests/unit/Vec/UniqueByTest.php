<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Vec;

final class UniqueByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testUniqueBy(array $expected, array $array, callable $scalar_fun): void
    {
        static::assertSame($expected, Vec\unique_by($array, $scalar_fun));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'saif'],
                ['a', 'b', 'c', 'd', 'saif', 'jack'],
                static fn(string $value): int => Str\length($value),
            ],
            [
                ['foo', 'bar', '@baz'],
                ['foo', '@foo', 'bar', '@bar', '@baz'],
                static fn(string $value): string => Str\replace($value, '@', ''),
            ],
        ];
    }
}
