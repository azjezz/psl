<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class MapWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapWithKey(array $expected, array $array, callable $function): void
    {
        $result = Vec\map_with_key($array, $function);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], ['a' => 1, 'b' => 2, 'c' => 3], static fn(string $_k, int $v): int => $v];
        yield [[1, 3, 5], [1, 2, 3], static fn(int $k, int $v): int => $k + $v];
        yield [[0, 4, 16], [1, 2, 3], static fn(int $k, int $v): int => $k * (2 ** $v)];
        yield [['1', '3', '5'], [1, 2, 3], static fn(int $k, int $v): string => (string) ($k + $v)];
        yield [[], [], static fn(int $k, int $v): string => (string) ($k + $v)];
    }
}
