<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class MapKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapKeys(array $expected, array $array, callable $function): void
    {
        $result = Dict\map_keys($array, $function);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn(int $k): int => $k];
        yield [[1, 2 => 2, 4 => 3], [1, 2, 3], static fn(int $k): int => $k * 2];
        yield [['0' => 1, '1' => 2, '2' => 3], [1, 2, 3], static fn(int $k): string => (string) $k];
        yield [[], [], static fn(int $k): string => (string) $k];
    }
}
