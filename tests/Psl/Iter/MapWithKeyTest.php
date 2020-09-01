<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class MapWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapWithKey(array $expected, iterable $iterable, callable $function): void
    {
        $result = Iter\map_with_key($iterable, $function);
        
        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 3, 5], [1, 2, 3], fn (int $k, int $v): int => $k + $v];
        yield [[0, 4, 16], [1, 2, 3], fn (int $k, int $v): int => $k * (2 ** $v)];
        yield [['1', '3', '5'], [1, 2, 3], fn (int $k, int $v): string => (string) ($k + $v)];
        yield [[], [], fn (int $k, int $v): string => (string) ($k + $v)];
        yield [[], Iter\Iterator::create([]), fn (int $k, int $v): string => (string) ($k + $v)];
        yield [['1', '3'], Iter\Iterator::create([1, 2]), fn (int $k, int $v): string => (string) ($k + $v)];
    }
}
