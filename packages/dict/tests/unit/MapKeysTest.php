<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class MapKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMapKeys(array $expected, array $array, callable $function): void
    {
        $result = Dict\map_keys::<int, int|string, int>($array, $function);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn(int $k): int => $k];
        yield [[1, 2 => 2, 4 => 3], [1, 2, 3], static fn(int $k): int => $k * 2];
        yield [['0' => 1, '1' => 2, '2' => 3], [1, 2, 3], static fn(int $k): string => (string) $k];
        yield [[], [], static fn(int $k): string => (string) $k];
    }
}
