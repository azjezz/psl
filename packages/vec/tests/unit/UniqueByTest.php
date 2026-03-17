<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Vec;

final class UniqueByTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testUniqueBy(array $expected, array $array, callable $scalarFun): void
    {
        static::assertSame($expected, Vec\unique_by($array, $scalarFun));
    }

    public static function provideData(): array
    {
        return [
            [
                ['a', 'saif'],
                ['a', 'b', 'c', 'd', 'saif', 'jack'],
                Str\length(...),
            ],
            [
                ['foo', 'bar', '@baz'],
                ['foo', '@foo', 'bar', '@bar', '@baz'],
                static fn(string $value): string => Str\replace($value, '@', ''),
            ],
        ];
    }
}
